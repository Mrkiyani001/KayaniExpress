<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewRequests\ApproveRequest;
use App\Http\Requests\ReviewRequests\CreateRequest;
use App\Http\Requests\ReviewRequests\DeleteRequest;
use App\Http\Requests\ReviewRequests\GetReviewRequest;
use App\Services\ReviewService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReviewController extends BaseController
{
    private $reviewService;
    public function __construct(ReviewService $reviewService) {
        $this->reviewService = $reviewService;
    }

    public function create(CreateRequest $request)
    {
        try {   
            DB::beginTransaction();
            $data = $request->validated();
            $user = Auth::user();
            $review = $this->reviewService->create_review($data, $user, $request);
            DB::commit();
            return $this->Response(true, 'Review created successfully', $review, 200);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->Response(false, $e->getMessage(), null, 500);
        }
    }
    public function get_review(GetReviewRequest $request){
        try{
            $data = $request->validated();
            $limit = (int)$request->input('limit', 10);
            $user = Auth::user();
            if(!$user){
                return $this->unauthorized();
            }
            $review = $this->reviewService->get_review($data, $limit);
            return $this->Response(true, 'Review fetched successfully', $review, 200);
        }catch(Exception $e){
            return $this->Response(false, $e->getMessage(), null, 500);
        }
    } 
    public function delete_review(DeleteRequest $request){
        try{
            DB::beginTransaction();
            $data = $request->validated();
            $user = Auth::user();
            if(!$user){
                return $this->unauthorized();
            }
            $review = $this->reviewService->delete_review($data);
            DB::commit();
            return $this->Response(true, 'Review deleted successfully', $review, 200);
        }catch(Exception $e){
            DB::rollBack();
            return $this->Response(false, $e->getMessage(), null, 500);
        }
    }
    public function pending_review(Request $request){
        try{
            $limit = (int) $request->input('limit', 10);
            $user = Auth::user();
            if(!$user){
                return $this->unauthorized();
            }
            $review = $this->reviewService->pending_review($limit);
            $data = $this->paginateData($review, $review->items());
            return $this->Response(true, 'Review fetched successfully', $data, 200);
        }catch(Exception $e){
            return $this->Response(false, $e->getMessage(), null, 500);
        }
    }  
    public function approve_review(ApproveRequest $request){
        try{
            DB::beginTransaction();
            $user = Auth::user();
            if(!$user){
                return $this->unauthorized();
            }
            $data = $request->validated();
            $review = $this->reviewService->approve_review($data, $user);
            DB::commit();
            return $this->Response(true, 'Review approved successfully', $review, 200);
        }catch(Exception $e){
            DB::rollBack();
            return $this->Response(false, $e->getMessage(), null, 500);
        }
    }  
}
