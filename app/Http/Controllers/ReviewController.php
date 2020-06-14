<?php

namespace App\Http\Controllers;

use App\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function getReviews(Request $request)
    {
        return response()->json(DB::select('CALL getReviews(? , ? , ? , ? , ? , ?)' ,[
                $request->offset,
                    $request->no,
                $request->item,
                'created_at',
                'DESC',
                1
        ]));
    }

    public function setReview(Request $request)
    {
        $item = $request->item ? $request->item : 0;
        $review = DB::select('CALL setReview(? , ? , ? , ? )' ,[
            $request->review,
            $request->user()->id,
            $item,
            $request->rate,
        ]);

        return response()->json(['success' => true , 'message' => 'review_placed_successfully']);
    }

    public function updateReview(Request $request , $id)
    {
        if($request->user()->id == Review::find($id)->user_id){
            $review = DB::select('CALL updateReview(? , ? , ? )' ,[
                $id,
                $request->review,
                $request->rate,
            ]);
    
            return response()->json(['success' => true , 'message' => 'review_placed_successfully']);
        }

        return response()->json(['success' => true , 'message' => 'dont_have_permission']);

        
    }

    // public function getRe
}
