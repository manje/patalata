<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\ModelFedi;

class Member extends Model
{
    use HasFactory;
    use ModelFedi;

    public $APtype='Collection';

    protected $fillable = [
        'actor', // campaña
        'object', // equipo
        #'role', en desuso, hay que eliminarlo con una migración
        'status' // ['admin','editor','Join','Invite']
    ];

}
    /**
    
    https://socialhub.activitypub.rocks/t/where-and-how-to-send-join-activity/4263/2
    
    Offer{Join}: “Bob offers to join the Project / Group” (an event: the offer stands)
    Accept{Join}: “Alice accepts Bob’s offer to join” (event too: accepted)
    Join{Person}: “Project / Group states that Bob joined as member” (yep, an event)

     */
