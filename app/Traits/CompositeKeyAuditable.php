<?php

namespace App\Traits;

trait CompositeKeyAuditable
{
    public function transformAudit(array $data): array
    {
        $compositeKey = [];
        foreach ($this->auditKeyColumns as $col) {
            $compositeKey[$col] = $this->getAttribute($col);
        }
        $data['auditable_id'] = json_encode($compositeKey);
        return $data;
    }
}
