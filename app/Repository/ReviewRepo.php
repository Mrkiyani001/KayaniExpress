<?php
namespace App\Repository;

use App\Models\OrderItem;
use App\Models\Review;

class ReviewRepo{
    public function create_review($data, $user , $images = null){
        $check_product = OrderItem::where('id', $data['order_item_id'])->where('delivery_status', 'delivered')->first();
        if(!$check_product){
            throw new \Exception('Product not delivered');
        }
        $review = Review::create([
            'user_id' => $user->id,
            'product_id' => $data['product_id'],
            'order_item_id' => $data['order_item_id'],
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'images' => $images ?? null,
        ]);
        return $review;
    }
    public function get_review($data, $limit){
        $review = Review::where('product_id', $data['product_id'])->where('is_approved', true)->paginate($limit);
        return $review;
    }

    public function automatic_update_review($id){
        $review = Review::where('id', $id)->firstOrFail();
        $review->update([
            'is_approved' => true,
        ]);
        return $review;
    }
    public function delete_review($data){
        $review = Review::Where('id',$data['id'])->firstOrFail();
        $review->delete();
        return $review;
    }
    public function pending_review($limit){
        $review = Review::where('is_approved', false)->paginate($limit);
        return $review;
    }
    public function approve_review($data, $user){
        if($user->hasRole(['Admin', 'SuperAdmin'])){
            $review = Review::where('id', $data['id'])->firstOrFail();
            $review->update([
                'is_approved' => true,
            ]);
            return $review;
        }else{
            throw new \Exception('You are not authorized to approve review');
        }
    
    }
    public function Unapproved(){
        $review = Review::where('is_approved', false)
        ->where('created_at', '<', now()->subMinutes(1)
        )->get();
        return $review;
    }
    
}