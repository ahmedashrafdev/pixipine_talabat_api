<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    public function details($id , $optionsVals = null)
    {
        $groups = DB::select('call getItemGroups(?)' , [$id]);
        // dd($groups);
        $result = [];
        if($groups){
            foreach($groups as $group)
        {
            $options =  DB::select('call  getItemGroupOption(? , ?)' , [$id , $group->id]);
            // dd($options);
            if($optionsVals){
                foreach($options as $option)
                {
                    $option->selected = in_array($option->id,$optionsVals) ? true : false;
                }
            }
            
            $form = ['group' => $group , 'options' => $options];
            array_push($result , $form);
            }
        }
        
        return $result;
    }
}
