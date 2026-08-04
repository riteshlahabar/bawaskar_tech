<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

abstract class PeopleModuleController extends AdminModuleController
{
    protected string $role;
    protected string $profileRelation;
    protected string $profileModel;
    protected array $profileFields = [];

    protected function recordsQuery(array $module): Builder
    {
        return User::query()->with($module['with'] ?? [$this->profileRelation])->where('role', $this->role);
    }

    protected function rules(array $module, ?Model $record = null): array
    {
        $rules = parent::rules($module, $record);
        if ($record) {
            $relationName = explode('.', $this->profileRelation)[0];
            $profileId = $record->{$relationName}?->getKey();
            if ($profileId) {
                foreach ($this->profileFields as $field) {
                    if (! isset($rules[$field])) continue;
                    $rules[$field] = array_map(static function ($rule) use ($profileId) {
                        if (is_string($rule) && str_starts_with($rule, 'unique:')) {
                            $parts = explode(',', $rule);
                            if (count($parts) >= 3) $parts[2] = (string) $profileId;
                            return implode(',', $parts);
                        }
                        return $rule;
                    }, $rules[$field]);
                }
            }
        }
        return $rules;
    }

    protected function persist(array $data, ?Model $record): Model
    {
        return DB::transaction(function () use ($data, $record): Model {
            $profile = [];
            foreach ($this->profileFields as $field) {
                if (array_key_exists($field, $data)) { $profile[$field] = $data[$field]; unset($data[$field]); }
            }
            $data['role'] = $this->role;
            if (! $record) {
                $data['status'] ??= $this->role === User::ROLE_DEALER ? 'pending_approval' : 'active';
                $data['password'] ??= Str::password(20);
                $record = User::query()->create($data);
            } else {
                $record->fill($data)->save();
            }
            if ($profile !== []) {
                $profile['user_id'] = $record->id;
                $this->profileModel::query()->updateOrCreate(['user_id' => $record->id], $profile);
            }
            return $record->fresh($this->profileRelation);
        });
    }

    protected function formData(Model $record, array $module): array
    {
        $data = parent::formData($record, $module);
        foreach ($this->profileFields as $field) $data[$field] = data_get($record, $this->profileRelation.'.'.$field);
        return $data;
    }
}
