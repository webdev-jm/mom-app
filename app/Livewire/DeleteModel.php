<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;

use App\Models\{
    User, Company, Role, MomType, Mom, Location
};

class DeleteModel extends Component
{
    public $password;
    public $error_message;
    public $model_id;
    public $name;
    public $model_route;
    public $type;

    protected $listeners = [
        'setDeleteModel' => 'setModel'
    ];

    /** @var array<string, array{model: class-string, route: string}> */
    protected array $modelMapping = [
        'Company' => ['model' => Company::class, 'route' => '/companies'],
        'User' => ['model' => User::class, 'route' => '/users'],
        'Role' => ['model' => Role::class, 'route' => '/roles'],
        'MomType' => ['model' => MomType::class, 'route' => '/mom-types'],
        'Mom' => ['model' => Mom::class, 'route' => '/moms'],
        'Location' => ['model' => Location::class, 'route' => '/locations'],
    ];

    public function render(): \Illuminate\View\View
    {
        return view('livewire.delete-model');
    }

    public function submitForm(): mixed
    {
        $this->error_message = '';

        $model = $this->resolveModel();
        $model->delete();

        activity('delete')
            ->performedOn($model)
            ->withProperties($model)
            ->log(':causer.name has deleted '.$this->type.' ['.$this->name.']');

        return redirect()->to($this->model_route)->with([
            'message_success' => $this->type.' ['.$this->name.'] was deleted successfully.'
        ]);
    }

    public function setModel(string $type, string $encryptedId): void
    {
        if (!isset($this->modelMapping[$type])) {
            throw new \InvalidArgumentException("Invalid model type: {$type}");
        }

        $this->type = $type;
        $this->model_id = decrypt($encryptedId);
        $this->model_route = $this->modelMapping[$type]['route'];

        $model = $this->resolveModel();
        $this->name = $model->name;
    }

    protected function resolveModel(): \Illuminate\Database\Eloquent\Model
    {
        $class = $this->modelMapping[$this->type]['model'];

        return $class::findOrFail($this->model_id);
    }
}
