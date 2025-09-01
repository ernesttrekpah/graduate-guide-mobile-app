<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use App\Models\{Subject, SubjectAlias};

// app/Console/Commands/CatalogueScan.php
class CatalogueScan extends Command
{
    protected $signature = 'catalogue:scan {path : Path to .txt or .json file} {--out= : Save report JSON to this path}';
    protected $description = 'Scan catalogue data and report unknown/unmapped subjects';

    public function handle(): int
    {
        $path = $this->argument('path');
        if (!File::exists($path)) {
            $this->error("File not found: $path");
            return self::FAILURE;
        }

        $raw = trim(File::get($path));
        $records = $this->parseRecords($raw);

        $unknown = [];
        foreach ($records as $rec) {
            foreach ($this->extractSubjectTokens($rec) as $token) {
                if (!$this->isKnownSubject($token)) {
                    $unknown[$token] = ($unknown[$token] ?? 0) + 1;
                }
            }
        }

        arsort($unknown);
        $this->info('Unknown subjects (count):');
        foreach ($unknown as $name => $count) {
            $this->line(" - {$name} ({$count})");
        }

        if ($out = $this->option('out')) {
            File::put($out, json_encode($unknown, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
            $this->info("Saved to: $out");
        }

        return self::SUCCESS;
    }

    protected function parseRecords(string $raw): array
    {
        if (Str::startsWith($raw, '[')) {
            $arr = json_decode($raw, true);
            return is_array($arr) ? $arr : [];
        }
        $rows = preg_split('/\r?\n/', $raw);
        $out = [];
        foreach ($rows as $line) {
            $line = trim($line);
            if ($line === '') continue;
            $obj = json_decode($line, true);
            if (is_array($obj)) $out[] = $obj;
        }
        return $out;
    }

    protected function extractSubjectTokens(array $rec): array
    {
        $tokens = [];

     $grab = function ($val) use (&$tokens, &$grab) {
    if (is_string($val) && $val !== '') {
        $val = str_replace(['–','—'], '-', $val);
        foreach (array_filter(array_map('trim', preg_split('/\s*,\s*/', $val))) as $part) {
            // split "X/Y" choices, strip "(A1-C6)"
            $label = preg_replace('/\((?:[^)]+)\)$/u', '', $part);
            foreach (explode('/', $label) as $piece) {
                $p = trim($piece);
                if ($p !== '') $tokens[] = $p;
            }
        }
    } elseif (is_array($val)) {
        foreach ($val as $entry) {
            if (is_string($entry)) { 
                $grab($entry); // recursive call OK now
            } elseif (is_array($entry)) {
                if (isset($entry['subject'])) $tokens[] = trim((string)$entry['subject']);
                if (isset($entry['name']))    $tokens[] = trim((string)$entry['name']);
                foreach (['or','subjects'] as $k) {
                    if (isset($entry[$k]) && is_array($entry[$k])) {
                        foreach ($entry[$k] as $s) $tokens[] = trim((string)$s);
                    }
                }
            }
        }
    }
};

        foreach (['core_subjects','core','core_requirements','elective_subjects','electives','elective_requirements'] as $k) {
            if (isset($rec[$k])) $grab($rec[$k]);
        }

        // optional: parse "Minimum B3 in X" patterns in additional_requirements
        if (!empty($rec['additional_requirements']) && is_string($rec['additional_requirements'])) {
            if (preg_match('/in\s+(.+?)$/i', $rec['additional_requirements'], $m)) {
                $tokens[] = trim($m[1]);
            }
        }

        // Dedupe
        return array_values(array_unique($tokens));
    }

    protected function isKnownSubject(string $name): bool
    {
        if (Subject::where('name',$name)->exists()) return true;
        if (SubjectAlias::where('alias',$name)->exists()) return true;
        if (SubjectAlias::whereRaw('LOWER(alias)=?', [strtolower($name)])->exists()) return true;
        return false;
    }
}
