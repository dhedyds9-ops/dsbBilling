<?php

namespace App\Services\ISP;

use App\Models\ISP\Router;
use App\Models\ISP\RadiusNas;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class RouterService extends NetworkInfrastructureService
{
    protected function getModelClass(): string
    {
        return Router::class;
    }

    public function create(array $data, User $user): Model
    {
        $router = parent::create($data, $user);
        $this->syncRadiusNas($router, $user);
        return $router;
    }

    public function update(Model $model, array $data, User $user): Model
    {
        $router = parent::update($model, $data, $user);
        $this->syncRadiusNas($router, $user);
        return $router;
    }

    public function delete(Model $model, User $user): void
    {
        // Delete corresponding RadiusNas before soft deleting Router
        RadiusNas::where('nas_ip_address', $model->ip_address)->delete();
        parent::delete($model, $user);
    }

    public function restore(Model $model, User $user): void
    {
        parent::restore($model, $user);
        $this->syncRadiusNas($model, $user);
    }

    private function syncRadiusNas(Router $router, User $user): void
    {
        if (empty($router->ip_address)) {
            return;
        }
        
        $radiusNas = RadiusNas::where('nas_ip_address', $router->ip_address)->first();
        
        if (!$radiusNas) {
            $radiusNas = new RadiusNas();
            $radiusNas->uuid = \Illuminate\Support\Str::uuid();
            $radiusNas->created_by = $user->id;
        }
        
        $radiusNas->nas_name = $router->name;
        $radiusNas->nas_ip_address = $router->ip_address;
        $radiusNas->nas_secret = $router->radius_secret ?? 'radius_secret'; // Fallback if null
        $radiusNas->nas_type = 'mikrotik';
        $radiusNas->status = $router->status;
        $radiusNas->updated_by = $user->id;
        $radiusNas->save();
    }
}
