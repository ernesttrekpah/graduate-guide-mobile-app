<?php
namespace App\Services;

use App\Models\GradeMapping;
use App\Models\GradeScale;
use RuntimeException;

class Grades
{
    protected GradeScale $scale;
    protected array $lookup;

    public function __construct(string $scaleName = 'WASSCE')
    {
        $this->scale  = GradeScale::where('name', $scaleName)->firstOrFail();
        $this->lookup = GradeMapping::where('scale_id', $this->scale->id)
            ->pluck('numeric_value', 'label')->mapWithKeys(function ($num, $label) {
            return [strtoupper($label) => (int) $num];
        })->toArray();
    }

    public function toNumeric(string $label): int
    {
        $key = strtoupper(trim($label));
        if (! isset($this->lookup[$key])) {
            throw new RuntimeException("Unknown grade label: {$label}");
        }
        return $this->lookup[$key];
    }
}
