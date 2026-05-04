<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MtTree;
use App\Models\Project;
use App\Models\Family;
use App\Models\ScientificName;
use App\Models\Tree;
use DOMDocument;

class KmlController extends Controller
{
    public function generateAllKml(Request $request)
    {
        // 1. Apply Filters (Same as Tree List)
        $query = MtTree::query();

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('tree_no')) {
            $query->where('tree_no', 'like', '%' . $request->tree_no . '%');
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [
                $request->from_date . ' 00:00:00', 
                $request->to_date . ' 23:59:59'
            ]);
        }

        $trees = $query->get();

        if ($trees->isEmpty()) {
            return redirect()->back()->with('error', 'No tree data found for selected filters.');
        }

        // Base URL for images
        $baseUrl = url('/');
        $schemaId = "All_Trees_Schema";
        $styleId = "tree_icon_style"; // ID for the icon style

        // Initialize DOM
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        // KML Root
        $kml = $dom->createElement('kml');
        $kml->setAttribute('xmlns', 'http://www.opengis.net/kml/2.2');
        $dom->appendChild($kml);

        // Document
        $document = $dom->createElement('Document');
        $kml->appendChild($document);

        // Name
        $docName = $dom->createElement('name', 'Tree Data Export');
        $document->appendChild($docName);

        // ==========================================
        // ✅ ADD CUSTOM TREE ICON STYLE HERE
        // ==========================================
        $style = $dom->createElement('Style');
        $style->setAttribute('id', $styleId);

        $iconStyle = $dom->createElement('IconStyle');
        
        // Scale (Icon size, 1.1 is slightly larger)
        $scale = $dom->createElement('scale', '1.1');
        $iconStyle->appendChild($scale);

        // Icon Link (Google Earth Standard Green Tree Icon)
        $icon = $dom->createElement('Icon');
        $href = $dom->createElement('href', 'http://maps.google.com/mapfiles/kml/pal2/icon4.png'); 
        $icon->appendChild($href);
        
        $iconStyle->appendChild($icon);
        $style->appendChild($iconStyle);

        // ✅ NEW ADDITION: LabelStyle to hide the text next to the icon on the map (scale = 0)
        $labelStyle = $dom->createElement('LabelStyle');
        $labelScale = $dom->createElement('scale', '0'); // 0 means hide text
        $labelStyle->appendChild($labelScale);
        $style->appendChild($labelStyle);

        $document->appendChild($style);
        // ==========================================

        // DEFINE SCHEMA
        $schema = $dom->createElement('Schema');
        $schema->setAttribute('name', 'All Trees Data');
        $schema->setAttribute('id', $schemaId);

        // Fields Definition
        $fields = [
            'Project_Name' => 'string',
            'Tree_No' => 'string',
            'Ward_Plot_No' => 'string',
            'Tree_Name' => 'string',
            'Scientific_Name' => 'string',
            'Family' => 'string',
            'Girth' => 'string',
            'Height' => 'string',
            'Canopy' => 'string',
            'Condition' => 'string',
            'Address' => 'string',
            'Image_Path' => 'string',
        ];

        foreach ($fields as $fieldName => $type) {
            $simpleField = $dom->createElement('SimpleField');
            $simpleField->setAttribute('name', $fieldName);
            $simpleField->setAttribute('type', $type);
            $displayName = $dom->createElement('displayName', str_replace('_', ' ', $fieldName));
            $simpleField->appendChild($displayName);
            $schema->appendChild($simpleField);
        }
        $document->appendChild($schema);

        // LOOP DATA
        foreach ($trees as $tree) {
            $project = Project::find($tree->project_id);
            $treeName = Tree::find($tree->tree_name);
            $scientific = ScientificName::find($tree->scientific_name);
            $family = Family::find($tree->family);

            $project_name = $project->project_name ?? 'N/A';
            $tree_name = $treeName->name ?? $tree->tree_name ?? 'N/A';
            $scientific_name = $scientific->scientific_name ?? $tree->scientific_name ?? 'N/A';
            $family_name = $family->family_name ?? $tree->family ?? 'N/A';

            // Process Images
            $imageUrls = [];
            $imgHtml = "";
            if (!empty($tree->all_captured_images)) {
                $decoded = json_decode($tree->all_captured_images, true);
                if (is_array($decoded)) {
                    foreach ($decoded as $imgPath) {
                        $path = str_replace('\\', '/', $imgPath);
                        $fullUrl = rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
                        $imageUrls[] = $fullUrl;
                        $imgHtml .= "<br/><br/><a href='{$fullUrl}'><img src='{$fullUrl}' width='300' /></a>";
                    }
                }
            }
            $imagePathString = implode(', ', $imageUrls);

            // Placemark
            $placemark = $dom->createElement('Placemark');

            // ✅ LINK PLACEMARK TO ICON STYLE
            $styleUrl = $dom->createElement('styleUrl', '#' . $styleId);
            $placemark->appendChild($styleUrl);

            // Is name tag ki wajah se sidebar aur popup me naam dikhega, 
            // par LabelStyle scale 0 ki wajah se map par nahi dikhega.
            $nameNode = $dom->createElement('name', "Tree " . $tree->tree_no);
            $placemark->appendChild($nameNode);

            if (!empty($imgHtml)) {
                $description = $dom->createElement('description');
                $description->appendChild($dom->createCDATASection($imgHtml));
                $placemark->appendChild($description);
            }

            // Extended Data
            $extendedData = $dom->createElement('ExtendedData');
            $schemaData = $dom->createElement('SchemaData');
            $schemaData->setAttribute('schemaUrl', '#' . $schemaId);

            $addData = function ($name, $val) use ($dom, $schemaData) {
                $val = $val ?? '';
                $simpleData = $dom->createElement('SimpleData', htmlspecialchars($val));
                $simpleData->setAttribute('name', $name);
                $schemaData->appendChild($simpleData);
            };

            $addData('Project_Name', $project_name);
            $addData('Tree_No', $tree->tree_no);
            $addData('Ward_Plot_No', $tree->ward_plot_no);
            $addData('Tree_Name', $tree_name);
            $addData('Scientific_Name', $scientific_name);
            $addData('Family', $family_name);
            $addData('Girth', $tree->girth);
            $addData('Height', $tree->height);
            $addData('Canopy', $tree->canopy);
            $addData('Condition', $tree->condition);
            $addData('Address', $tree->address);
            $addData('Image_Path', $imagePathString);

            $extendedData->appendChild($schemaData);
            $placemark->appendChild($extendedData);

            // Coordinates
            if (!empty($tree->latitude) && !empty($tree->longitude)) {
                $point = $dom->createElement('Point');
                $coordinates = $dom->createElement('coordinates', "{$tree->longitude},{$tree->latitude},0");
                $point->appendChild($coordinates);
                $placemark->appendChild($point);
            }

            $document->appendChild($placemark);
        }

        // Save & Download
        $filename = 'filtered_trees_' . time() . '.kml';
        if (!file_exists(storage_path("app/public"))) {
            mkdir(storage_path("app/public"), 0755, true);
        }
        $filePath = storage_path("app/public/{$filename}");
        $dom->save($filePath);

        return response()->download($filePath, $filename, [
            'Content-Type' => 'application/vnd.google-earth.kml+xml',
        ])->deleteFileAfterSend(true);
    }
}