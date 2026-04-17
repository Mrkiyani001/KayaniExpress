<?php
namespace App\Services;

use App\Repository\ReviewRepo;
use App\Traits\UploadTraits;
use Exception;

class ReviewService{
    use UploadTraits;
    private $reviewRepo;
    public function __construct(ReviewRepo $reviewRepo){
        $this->reviewRepo = $reviewRepo;
    }
    public function create_review($data, $user, $request){
        try{
            $images = [];
            if($request->hasFile('images')){
                foreach ($request->file('images') as $image) {
                    $images[] = $this->upload($image, 'reviews');
                }
            }
        $review = $this->reviewRepo->create_review($data, $user, $images);
            return $review;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function get_review($data, $limit){
        try{
            $review = $this->reviewRepo->get_review($data, $limit);
            return $review;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function delete_review($data){
        try{
            $review = $this->reviewRepo->delete_review($data);
            return $review;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function pending_review($limit){
        try{
            $review = $this->reviewRepo->pending_review($limit);
            return $review;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function approve_review($data, $user){
        try{
            $review = $this->reviewRepo->approve_review($data, $user);
            return $review;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
}