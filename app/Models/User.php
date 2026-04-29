<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany; // Add this
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use Spatie\Permission\Traits\HasRoles;

use Illuminate\Support\Facades\Session;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasRoles;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'name',
        'email',
        'password',
        'profile_pic',
        'dark_mode',
        'google_id',
        'hub_user_id',
        'last_activity',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Dynamically set the database connection based on the session.
     */
    public function getConnectionName()
    {
        return Session::get('db_connection', 'mysql'); // Default to 'mysql' if not set
    }

    public function isOnline() {
        return $this->last_activity >= now()->subMinutes(2);
    }

    public function adminlte_image()
    {
        // random image
        return !empty($this->profile_pic) ? asset($this->profile_pic) : asset('images/Default_pfp.svg.png');
    }

    public function adminlte_desc()
    {
        $roleNames = $this->getRoleNames()->toArray();
        if (empty($roleNames)) {
            return '-';
        }
        return implode(', ', $roleNames);
    }

    public function adminlte_profile_url()
    {
        return 'profile/'.encrypt($this->id);
    }

    public function company() {
        return $this->belongsTo('App\Models\Company');
    }

    public function locations() {
        return $this->belongsToMany('App\Models\Location');
    }

    public function moms() {
        return $this->belongsToMany('App\Models\Mom');
    }

    public function mom_details() {
        return $this->belongsToMany('App\Models\MomDetail', 'mom_responsibles');
    }
}
