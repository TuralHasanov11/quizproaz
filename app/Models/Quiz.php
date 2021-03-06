<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class Quiz extends Model
{
    use HasFactory;
    
    protected $fillable=[
        'title', 'description', 'finished_at', 'status', 'slug'
    ];

    protected $dates=['finished_at'];


    protected $appends = ['users_details', 'current_user'];

    // public function getFinishedAtAttribute($date){
    //     return $date ? Carbon::parse($date) : null;
    // }

    public function getCreatedAt(){
        return $this->created_at->isoFormat('ll');
    }

    public function getFinishedAt(){
        return $this->finished_at->isoFormat('ll');
    }

    public function getCurrentUserAttribute(){

        $rank=0;
        foreach ($this->users()->get() as $key => $user) {
            if($user->id === auth()->id()){
                $rank = $key+1;
                break;
            }
        }
        
        if($data = $this->users()->find(auth()->id())){
            $data->rank=$rank;
        }
        
        return $data;
    } 

    public function getUsersDetailsAttribute(){
        return [
            'average_score'=>round(collect($this->users()->get())->pluck('pivot')->avg('score')),
            'users_count'=>$this->users()->count(),
        ];
    }

    public function questions(){
        return $this->hasMany('App\Models\Question');              
    }

    public function users(){
        return $this->belongsToMany('App\Models\User', 'quiz_user')
                        ->withPivot('score','correct_answers_count', 'wrong_answers_count')
                        ->withTimestamps();
    }

    public function topTenUsers(){
        return $this->belongsToMany('App\Models\User', 'quiz_user')
                        ->withPivot('score','correct_answers_count', 'wrong_answers_count')
                        ->orderByDesc('score')
                        ->take(10)
                        ->withTimestamps();
    }
}
