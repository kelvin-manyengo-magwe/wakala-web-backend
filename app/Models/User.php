<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Role;
use App\Models\AirtelTransaction;
use App\Models\HalotelTransaction;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;




class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password', 'phone_no', 'location', 'till_no',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'till_no' => 'array',
        ];
    }

    public function canAccessFilament(): bool
   {
       return $this->hasRole('admin');
   }

         public function airtelTransactions()
            {
                return $this->hasMany(AirtelTransaction::class);
            }

      public function halotelTransactions()
          {
              return $this->hasMany(HalotelTransaction::class);
          }

          /**
    * Shops this User (Wakala) is assigned to operate.
    */
         public function assignedShops(): BelongsToMany
         {
              return $this->belongsToMany(Shop::class, 'shop_user', 'user_id', 'shop_id')
                         ->withTimestamps(); // If your pivot table has timestamps
         }

       /*public function roles()
            {
                return $this->belongsToMany(Role::class);
            }*/

}
