<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\OtpRequest;
use App\Models\Project;
use App\Models\User;
use App\Models\UserRating;
use App\Services\AuthService;
use App\Services\OtpService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyOtpMail;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    use ApiResponse;

    protected $otpService;

    protected $authService;

    public function __construct(OtpService $otpService, AuthService $authService)
    {
        $this->otpService = $otpService;
        $this->authService = $authService;
    }

    /**
     * Admin / Staff Login (Email/Password)
     */
    public function extra_login(LoginRequest $request)
    {
        try {
            if (! Auth::attempt($request->only('email', 'password'))) {
                return $this->error('Invalid credentials', 401);
            }

            /** @var \App\Models\User $user */
            $user = Auth::user();

            if ($user->role_id == 1) {
                Auth::logout();

                return $this->error('Invalid credentials for this application.', 401);
            }

            if ($user->role_id == 3) {
                Auth::logout();

                return $this->error('Unauthorized: Customers must login using Mobile OTP.', 403, ['is_verified' => $user->is_verified]);
            }

            if ($user->role_id != 2) {
                Auth::logout();

                return $this->error('Unauthorized role.', 403);
            }

            $token = $user->createToken('auth_token')->plainTextToken;
            
            return $this->success([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ], 'Login successfully');
        } catch (Exception $e) {
            Log::error('Staff Login Error: ' . $e->getMessage());

            return $this->error('Something went wrong.', 500);
        }
    }

    /**
     * Send OTP to Phone
     */
    public function send_otp(OtpRequest $request)
    {
        try {
            $email = $request->email;
            $otp = (string) rand(1000, 9999);

            // Verify if existing staff is trying to login via OTP (usually not allowed if they are role 2)
            $userCheck = User::where('email', $email)->first();
            if ($userCheck && $userCheck->role_id == 2 && $userCheck->status != 1) {
                return $this->error('Your account is blocked.', 403);
            }

            // Send OTP via email using VerifyOtpMail
            Mail::to($email)->send(new VerifyOtpMail($otp));

            // Register or update user with OTP (using email)
            $result = $this->authService->registerOrRetrieveUser($email, $otp, 'email');

            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully to email.',
                'user_type' => $result['is_new'] ? 'new' : 'existing',
            ]);
        } catch (Exception $e) {
            Log::error('API Send OTP Error: ' . $e->getMessage());

            return $this->error('Could not send OTP.', 500);
        }
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(OtpRequest $request)
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (! $user) {
                return $this->error('User not found', 404);
            }

            if ($this->authService->verifyUserOtp($user, $request->otp, 'email')) {
                $isNewUser = ($user->is_verified == 0);
                $token = $user->createToken('auth_token')->plainTextToken;

                return response()->json([
                    'success' => true,
                    'message' => 'OTP Verified Successfully',
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'is_new_user' => $isNewUser,
                    'is_verified' => $user->is_verified,
                    'user' => $user,
                ]);
            }

            return $this->error('Invalid OTP', 401);
        } catch (Exception $e) {
            Log::error('API Verify OTP Error: ' . $e->getMessage());

            return $this->error('Verification failed.', 500);
        }
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if ($user) {
            /** @var \Laravel\Sanctum\PersonalAccessToken $token */
            $token = $user->currentAccessToken();
            if ($token) {
                $token->delete();
            }
        }

        return $this->success(null, 'Logged out successfully');
    }

    /**
     * Get Authenticated User Details
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Register OTP (Step 1)
     */
    public function register_otp(OtpRequest $request)
    {
        return $this->send_otp($request); // Shared logic
    }

    /**
     * User Registration (Step 2)
     */
    public function user_register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'phone' => 'required|numeric|unique:users,phone',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->phone . '@mobile.temp',
                'password' => Hash::make($request->password),
                'role_id' => 3,
                'is_verified' => 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'user' => $user
            ], 201);
        } catch (Exception $e) {
            return $this->error('Registration failed', 500);
        }
    }

    /**
     * Project Assign Officer
     */
     /**
     * Project list fetched by mobile app (Merged Logic)
     */
    public function project_assign_officer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_id' => 'required',
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        try {
            $user_id = $request->user_id;
            $role_id = $request->role_id;
            $projects = collect();

            $query = Project::with(['state', 'fieldOfficer'])->withCount('trees');

            if ($role_id == 3) {
                $query->where('extra_user', $user_id);
            } elseif ($role_id == 2) {
                $query->whereRaw("JSON_CONTAINS(field_officer_id, '\"$user_id\"')");
            }
            
            $projects = $query->get();

            // Fetch active tree price (Old compatibility)
            $activeTreePrice = \App\Models\TreePrice::where('is_active', 1)->orderBy('id', 'desc')->value('price') ?? 0;

            return response()->json([
                'success' => true,
                'message' => 'Project list fetched successfully.',
                'active_tree_price' => number_format((float)$activeTreePrice, 2, '.', ''),
                'count' => $projects->count(),
                'data' => $projects
            ]);
        } catch (Exception $e) {
            return $this->error('Something went wrong: ' . $e->getMessage(), 500);
        }
    }


    /**
     * User Ratings
     */
    public function userRatings($user_id)
    {
        $ratings = UserRating::where('user_id', $user_id)->with('rater')->get();

        return $this->success($ratings);
    }
}
