<?php

namespace App\Http\Controllers\Api;

use App\Exports\ProjectTreeExport;
use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\MtTree;
use App\Models\Project;
use App\Models\ScientificName;
use App\Models\Tree;
use App\Models\TreePrice;
use App\Models\User;
use App\Models\UserFreeTree;
use App\Models\UserPaidTree;
use Barryvdh\DomPDF\Facade\Pdf;
use DOMDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use ZipArchive;

class ProjectExportController extends Controller
{
    /**
     * Check tree access for Role-3 user
     */
    private function checkRole3Access(int $userId, array $requestedTreeIds): array
    {
        $freeRecord = UserFreeTree::getOrCreate($userId);
        $paidTreeIds = UserPaidTree::where('user_id', $userId)
            ->whereIn('mt_tree_id', $requestedTreeIds)
            ->pluck('mt_tree_id')
            ->toArray();

        $alreadyFreeIds = array_values(array_intersect($requestedTreeIds, $freeRecord->tree_ids ?? []));
        $alreadyAccessible = array_unique(array_merge($alreadyFreeIds, $paidTreeIds));

        $newTreeIds = array_values(array_diff($requestedTreeIds, $alreadyAccessible));
        $remainingFreeSlots = $freeRecord->remainingFreeSlots();
        $canGetFree = array_slice($newTreeIds, 0, $remainingFreeSlots);
        $needsPayment = array_values(array_diff($newTreeIds, $canGetFree));

        return [
            'free_record' => $freeRecord,
            'already_accessible' => $alreadyAccessible,
            'can_get_free' => $canGetFree,
            'needs_payment' => $needsPayment,
            'remaining_slots' => $remainingFreeSlots,
        ];
    }

    /**
     * Get Export Links with Access Management
     */
    public function get_project_export_links(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'user_id' => 'required|exists:users,id',
            'tree_ids' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $projectId = $request->project_id;
        $userId = $request->user_id;
        $user = User::find($userId);

        $query = MtTree::where('project_id', $projectId);
        if ($request->filled('tree_ids')) {
            $query->whereIn('id', $request->tree_ids);
        }
        $requestedTreeIds = $query->pluck('id')->toArray();

        if (empty($requestedTreeIds)) {
            return response()->json(['success' => false, 'message' => 'No trees found.'], 404);
        }

        if ($user->role_id != 3) {
            return response()->json([
                'success' => true,
                'access' => 'full',
                'links' => $this->buildLinks(['project_id' => $projectId, 'tree_ids' => implode(',', $requestedTreeIds)]),
            ]);
        }

        // Customer Access Logic
        $access = $this->checkRole3Access($userId, $requestedTreeIds);
        $newlyGrantedFree = ! empty($access['can_get_free']) ? $access['free_record']->addFreeTreeIds($access['can_get_free']) : [];
        $accessibleIds = array_unique(array_merge($access['already_accessible'], $newlyGrantedFree));

        $paymentInfo = null;
        if (! empty($access['needs_payment'])) {
            $pricePerTree = TreePrice::active()->orderBy('id', 'desc')->value('price') ?? 0;
            $paymentInfo = [
                'tree_ids' => array_values($access['needs_payment']),
                'tree_count' => count($access['needs_payment']),
                'price_per_tree' => $pricePerTree,
                'total_amount' => $pricePerTree * count($access['needs_payment']),
            ];
        }

        return response()->json([
            'success' => true,
            'access' => empty($access['needs_payment']) ? 'full' : 'partial',
            'links' => ! empty($accessibleIds) ? $this->buildLinks(['project_id' => $projectId, 'tree_ids' => implode(',', $accessibleIds)]) : null,
            'payment_required' => $paymentInfo,
        ]);
    }

    private function buildLinks(array $queryParams): array
    {
        return [
            'pdf' => route('api.export.pdf', $queryParams),
            'excel' => route('api.export.excel', $queryParams),
            'kml' => route('api.export.kml', $queryParams),
            'imgs_zip' => route('api.export.imgs', $queryParams),
        ];
    }

    /**
     * Download PDF
     */
    public function downloadPdf(Request $request, $project_id)
    {
        $project = Project::findOrFail($project_id);
        $query = MtTree::where('project_id', $project_id);
        if ($request->has('tree_ids')) {
            $query->whereIn('id', explode(',', $request->tree_ids));
        }

        $trees = $query->get()->sortBy(fn($t) => (int) preg_replace('/[^0-9]/', '', $t->tree_no))->values();
        if ($trees->isEmpty()) {
            return response()->json(['message' => 'No trees found.'], 404);
        }

        $projectBy = User::find($project->extra_user)?->name ?? '-';
        $address = $trees->first()->address ?? '-';
        $location = ($trees->first()->latitude ?? '') . ', ' . ($trees->first()->longitude ?? '');

        return Pdf::loadView('exports.project_trees_pdf', compact('project', 'trees', 'projectBy', 'address', 'location'))
            ->download("Project_{$project_id}_Trees.pdf");
    }

    /**
     * Download Excel
     */
    public function downloadExcel(Request $request, $project_id)
    {
        $treeIdsArray = $request->has('tree_ids') ? explode(',', $request->tree_ids) : [];

        return Excel::download(new ProjectTreeExport($project_id, $treeIdsArray), "Project_{$project_id}_Trees.xlsx");
    }

    /**
     * Download KML
     */
    public function downloadKml(Request $request, $project_id)
    {
        $project = Project::find($project_id);
        $query = MtTree::where('project_id', $project_id);
        if ($request->has('tree_ids')) {
            $query->whereIn('id', explode(',', $request->tree_ids));
        }

        $trees = $query->get()->sortBy(fn($t) => (int) preg_replace('/[^0-9]/', '', $t->tree_no))->values();
        if ($trees->isEmpty()) {
            return response()->json(['message' => 'No trees found.'], 404);
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;
        $kml = $dom->createElementNS('http://www.opengis.net/kml/2.2', 'kml');
        $dom->appendChild($kml);
        $document = $kml->appendChild($dom->createElement('Document'));
        $document->appendChild($dom->createElement('name', $project->project_name ?? 'Project Data'));

        // Style
        $style = $dom->createElement('Style');
        $style->setAttribute('id', 'tree_icon_style');
        $document->appendChild($style);
        $iconStyle = $style->appendChild($dom->createElement('IconStyle'));
        $iconStyle->appendChild($dom->createElement('scale', '1.1'));
        $iconStyle->appendChild($dom->createElement('Icon'))->appendChild($dom->createElement('href', 'http://maps.google.com/mapfiles/kml/pal2/icon4.png'));

        $baseUrl = url('/') . '/';
        foreach ($trees as $tree) {
            $tName = Tree::find($tree->tree_name)->name ?? 'N/A';
            $sName = ScientificName::find($tree->scientific_name)->scientific_name ?? 'N/A';
            $fName = Family::find($tree->family)->family_name ?? 'N/A';

            $images = $tree->all_captured_images ?? [];
            $imageHtml = '';
            foreach ((array) $images as $idx => $img) {
                $imgUrl = $baseUrl . ltrim($img, '/');
                $imageHtml .= "<div style='margin:6px 0;'><a href='" . htmlspecialchars($imgUrl) . "' target='_blank' style='background:#2e7d32;color:#fff;padding:8px 14px;text-decoration:none;border-radius:6px;font-size:13px;'>🌿 View Image " . ($idx + 1) . '</a></div>';
            }

            $placemark = $document->appendChild($dom->createElement('Placemark'));
            $placemark->appendChild($dom->createElement('name', "{$tree->tree_no} - {$tName}"));
            $placemark->appendChild($dom->createElement('styleUrl', '#tree_icon_style'));

            $descHtml = "<table border='1' cellpadding='6' cellspacing='0' style='border-collapse:collapse;font-size:13px;width:100%;'>
                <tr><th align='left' style='background:#f2f2f2;'>Tree No</th><td>{$tree->tree_no}</td></tr>
                <tr><th align='left' style='background:#f2f2f2;'>Tree Name</th><td>{$tName}</td></tr>
                <tr><th align='left' style='background:#f2f2f2;'>Scientific Name</th><td>{$sName}</td></tr>
                <tr><th align='left' style='background:#f2f2f2;'>Family</th><td>{$fName}</td></tr>
                <tr><th align='left' style='background:#f2f2f2;'>Address</th><td>{$tree->address}</td></tr>
                <tr><th align='left' style='background:#f2f2f2;'>Images</th><td>{$imageHtml}</td></tr>
            </table>";
            $placemark->appendChild($dom->createElement('description'))->appendChild($dom->createCDATASection($descHtml));

            if ($tree->latitude && $tree->longitude) {
                $placemark->appendChild($dom->createElement('Point'))->appendChild($dom->createElement('coordinates', "{$tree->longitude},{$tree->latitude},0"));
            }
        }

        return response()->stream(fn() => print($dom->saveXML()), 200, [
            'Content-Type' => 'application/vnd.google-earth.kml+xml',
            'Content-Disposition' => "attachment; filename=Project_{$project_id}_Trees.kml",
        ]);
    }

    /**
     * Download Images Zip
     */
    public function downloadImgsZip(Request $request, $project_id)
    {
        set_time_limit(0);
        $query = MtTree::where('project_id', $project_id);
        if ($request->has('tree_ids')) {
            $query->whereIn('id', explode(',', $request->tree_ids));
        }

        $trees = $query->with('project')->get();
        if ($trees->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No images found.'], 404);
        }

        $zipFileName = "Tree_Images_project_{$project_id}_" . date('d-m-Y_H-i') . '.zip';
        $zipPath = storage_path("app/public/{$zipFileName}");
        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE) === true) {
            foreach ($trees as $tree) {
                $images = $tree->all_captured_images ?? [];
                foreach ((array) $images as $imagePath) {
                    $fullPath = public_path($imagePath);
                    if (File::exists($fullPath) && is_file($fullPath)) {
                        $zip->addFile($fullPath, "Trees/Tree_{$tree->tree_no}_" . basename($imagePath));
                    }
                }
            }
            $zip->close();
        }

        return File::exists($zipPath) ? response()->download($zipPath)->deleteFileAfterSend(true) : response()->json(['success' => false], 500);
    }
}
