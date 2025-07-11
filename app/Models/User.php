<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use App\Models\BranchUser;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    //funcion para saber si tiene asignada la sucursal
    public function hasBranch($branch_id){
        $branch = BranchUser::where('branch_id', $branch_id)->where('user_id', $this->id)->first();
        if(is_object($branch)){
            return true;
        }
        return false;
    }

    //funcion para obtener el roles
    public function getTurno(){
        return $this->hasOne('App\Models\Turno', 'id', 'turno_id');
    }

    //funcion para obtener el roles
    public function getBranch(){
        return $this->hasOne('App\Models\Branch', 'id', 'branch_id');
    }

    //funcion para obtener las sucursales asignadas
    public function getBranchs(){
        return $this->hasMany('App\Models\BranchUser', 'user_id', 'id');
    }

    //funcion para obtener los roles
    public function getRoles(){
        return $this->hasMany('App\Models\UserRole', 'user_id', 'id');
    }

    
    //Incializamos roles para el uso en hasRoles y hasAnyRole
    public function branchs(){
        return $this->belongsToMany('App\Models\BranchUser')->withTimesTamps();
    }
    
    //funcion para saber si tiene varios roles
    public function hasAnyRole($roles)
    {
        if (is_array($roles)) {
            foreach ($roles as $role) {
                if ($this->hasRole($role)) {
                    return true;
                }
            }
        } else {
            if ($this->hasRole($roles)) {
                return true;
            }
        }
        
        return false;
    }
    
    //funcion para saber si tiene un rol en especifico
    public function hasRole($roles)
    {   
        if($this->roles()->where('name', $roles)->first()) {
            return true;
        }
        return false;
    }
    
    //Incializamos roles para el uso en hasRoles y hasAnyRole
    public function roles(){
        return $this->belongsToMany('App\Models\Role')->withTimesTamps();
    }

    public function hasPermissionTo($permissionName)
    {
        foreach ($this->roles as $role) {
            if ($role->permissions->where('name', $permissionName)->isNotEmpty()) {
                return true;
            }
        }
        return false;
    }

    public function hasPermissionThroughModule($module, $submodule = null, $action = null)
    {   
        if($action && $submodule){
            return $this->roles()->whereHas('permissions', function($q) use ($module, $submodule, $action) {
                $q->where('module', $module)->where('submodule', $submodule)->where('action', $action);
            })->exists();
        }

        if($module){
            return $this->roles()->whereHas('permissions', function($q) use ($module, $submodule, $action) {
                $q->where('module', $module);
            })->exists();
        }

    }
}
