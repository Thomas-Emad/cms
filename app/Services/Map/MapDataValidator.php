<?php

namespace App\Services\Map;

/**
 * Validates a hotel map document (the shape in resources/js/Map/types.ts).
 *
 * Pure PHP on purpose (no framework dependencies) so it is trivially testable.
 * Returns hard `errors` (the map must not be saved) and soft `warnings`
 * (saved, but something is probably wrong - e.g. a location nobody can walk to).
 */
final class MapDataValidator
{
    public const CATEGORIES = ['reception', 'lobby', 'dining', 'cafe', 'wellness', 'fitness', 'pool', 'meeting', 'room', 'facility', 'transport', 'restroom', 'entrance', 'other'];
    public const NODE_TYPES = ['walk', 'elevator', 'stairs', 'entrance'];
    public const AREA_KINDS = ['building', 'corridor', 'room', 'public', 'service', 'water', 'outdoor'];
    public const REF_TYPES = ['facility', 'restaurant', 'room'];

    private const MAX_FLOORS = 40;
    private const MAX_NODES = 5000;
    private const MAX_LOCATIONS = 3000;
    private const MAX_ERRORS = 40;

    /** @var string[] */
    private array $errors = [];
    /** @var string[] */
    private array $warnings = [];

    /** @return array{errors: string[], warnings: string[]} */
    public function validate(mixed $data): array
    {
        $this->errors = [];
        $this->warnings = [];

        if (! is_array($data) || array_is_list($data)) {
            return $this->result('The map must be a JSON object.');
        }
        if (($data['version'] ?? null) !== 1) {
            $this->err('"version" must be 1.');
        }
        $mpu = $data['meters_per_unit'] ?? null;
        if (! is_numeric($mpu) || $mpu <= 0) {
            $this->err('"meters_per_unit" must be a number greater than 0 (e.g. 0.1 means 10 map units = 1 metre).');
        }
        foreach (['floors', 'nodes', 'locations'] as $key) {
            if (! isset($data[$key]) || ! is_array($data[$key]) || ! array_is_list($data[$key])) {
                $this->err("\"{$key}\" must be a list.");
            }
        }
        if ($this->errors) {
            return $this->result();
        }
        if (count($data['floors']) < 1 || count($data['floors']) > self::MAX_FLOORS) {
            $this->err('A map needs between 1 and ' . self::MAX_FLOORS . ' floors.');
        }
        if (count($data['nodes']) > self::MAX_NODES || count($data['locations']) > self::MAX_LOCATIONS) {
            $this->err('The map is too large.');
        }
        if ($this->errors) {
            return $this->result();
        }

        $floors = $this->floors($data['floors']);
        $nodes = $this->nodes($data['nodes'], $floors);
        $locations = $this->locations($data['locations'], $floors, $nodes);

        $start = $data['default_start'] ?? null;
        if ($start !== null && ! isset($locations[$start])) {
            $this->err("\"default_start\" refers to an unknown location \"{$start}\".");
        }

        if (! $this->errors) {
            $this->reachability($nodes, $locations, $start);
        }

        return $this->result();
    }

    /** @return array<string, array> */
    private function floors(array $list): array
    {
        $out = [];
        foreach ($list as $i => $f) {
            $p = "floors[$i]";
            if (! is_array($f) || ! $this->id($f['id'] ?? null)) {
                $this->err("$p needs a text \"id\".");
                continue;
            }
            if (isset($out[$f['id']])) {
                $this->err("Duplicate floor id \"{$f['id']}\".");
                continue;
            }
            foreach (['label', 'name'] as $k) {
                if (! is_string($f[$k] ?? null) || $f[$k] === '') {
                    $this->err("$p (\"{$f['id']}\") needs a \"$k\".");
                }
            }
            foreach (['level', 'width', 'height'] as $k) {
                if (! is_numeric($f[$k] ?? null)) {
                    $this->err("$p (\"{$f['id']}\") needs a numeric \"$k\".");
                }
            }
            if (is_numeric($f['width'] ?? null) && is_numeric($f['height'] ?? null) && ($f['width'] <= 0 || $f['height'] <= 0)) {
                $this->err("$p (\"{$f['id']}\") width/height must be positive.");
            }
            if (! isset($f['areas']) || ! is_array($f['areas'])) {
                $this->err("$p (\"{$f['id']}\") needs an \"areas\" list (may be empty).");
            } else {
                foreach ($f['areas'] as $j => $a) {
                    if (! is_array($a) || ! in_array($a['kind'] ?? null, self::AREA_KINDS, true)) {
                        $this->err("$p.areas[$j] has an unknown \"kind\".");
                    } elseif (! $this->numbers($a, ['x', 'y', 'w', 'h'])) {
                        $this->err("$p.areas[$j] needs numeric x, y, w, h.");
                    }
                }
            }
            $out[$f['id']] = $f;
        }
        return $out;
    }

    /** @return array<string, array> */
    private function nodes(array $list, array $floors): array
    {
        $out = [];
        foreach ($list as $i => $n) {
            $p = "nodes[$i]";
            if (! is_array($n) || ! $this->id($n['id'] ?? null)) {
                $this->err("$p needs a text \"id\".");
                continue;
            }
            $id = $n['id'];
            if (isset($out[$id])) {
                $this->err("Duplicate node id \"$id\".");
                continue;
            }
            if (! isset($floors[$n['floor'] ?? null])) {
                $this->err("Node \"$id\" is on an unknown floor.");
            } elseif ($this->numbers($n, ['x', 'y'])) {
                $f = $floors[$n['floor']];
                if ($n['x'] < 0 || $n['y'] < 0 || (is_numeric($f['width'] ?? null) && $n['x'] > $f['width']) || (is_numeric($f['height'] ?? null) && $n['y'] > $f['height'])) {
                    $this->err("Node \"$id\" lies outside its floor.");
                }
            } else {
                $this->err("Node \"$id\" needs numeric x and y.");
            }
            if (! in_array($n['type'] ?? null, self::NODE_TYPES, true)) {
                $this->err("Node \"$id\" has an unknown type.");
            }
            if (! isset($n['connections']) || ! is_array($n['connections'])) {
                $this->err("Node \"$id\" needs a \"connections\" list.");
            }
            if (isset($n['door']) && (! is_array($n['door']) || ! $this->numbers($n['door'], ['x', 'y']))) {
                $this->err("Node \"$id\" has an invalid \"door\".");
            }
            $out[$id] = $n;
        }

        foreach ($out as $id => $n) {
            foreach (is_array($n['connections'] ?? null) ? $n['connections'] : [] as $c) {
                if (! is_string($c) || ! isset($out[$c])) {
                    $this->err("Node \"$id\" connects to unknown node \"" . (is_scalar($c) ? $c : '?') . '".');
                    continue;
                }
                if (($n['floor'] ?? null) !== ($out[$c]['floor'] ?? null)) {
                    $ok = in_array($n['type'] ?? '', ['elevator', 'stairs'], true) && ($n['type'] === ($out[$c]['type'] ?? null));
                    if (! $ok) {
                        $this->err("Node \"$id\" links to \"$c\" on another floor - only elevator-to-elevator or stairs-to-stairs links may cross floors.");
                    }
                }
            }
        }
        return $out;
    }

    /** @return array<string, array> */
    private function locations(array $list, array $floors, array $nodes): array
    {
        $out = [];
        foreach ($list as $i => $l) {
            $p = "locations[$i]";
            if (! is_array($l) || ! $this->id($l['id'] ?? null)) {
                $this->err("$p needs a text \"id\".");
                continue;
            }
            $id = $l['id'];
            if (isset($out[$id])) {
                $this->err("Duplicate location id \"$id\".");
                continue;
            }
            if (! is_string($l['name'] ?? null) || trim($l['name']) === '') {
                $this->err("Location \"$id\" needs a name.");
            }
            if (! in_array($l['category'] ?? null, self::CATEGORIES, true)) {
                $this->err("Location \"$id\" has an unknown category.");
            }
            if (! isset($floors[$l['floor'] ?? null])) {
                $this->err("Location \"$id\" is on an unknown floor.");
            } elseif (! $this->numbers($l, ['x', 'y'])) {
                $this->err("Location \"$id\" needs numeric x and y.");
            }
            if (! empty($l['node'])) {
                if (! isset($nodes[$l['node']])) {
                    $this->err("Location \"$id\" uses unknown node \"{$l['node']}\".");
                } elseif (($nodes[$l['node']]['floor'] ?? null) !== ($l['floor'] ?? null)) {
                    $this->err("Location \"$id\" uses a node on a different floor.");
                }
            } elseif (isset($floors[$l['floor'] ?? null]) && ! $this->floorHasNode($nodes, $l['floor'])) {
                $this->err("Location \"$id\" is on a floor that has no walkway nodes.");
            }
            if (isset($l['ref'])) {
                $ok = is_array($l['ref']) && in_array($l['ref']['type'] ?? null, self::REF_TYPES, true) && is_string($l['ref']['slug'] ?? null) && $l['ref']['slug'] !== '';
                if (! $ok) {
                    $this->err("Location \"$id\" has an invalid \"ref\" (needs type facility|restaurant|room and a slug).");
                }
            }
            foreach (['image'] as $k) {
                if (isset($l[$k]) && ! is_string($l[$k])) {
                    $this->err("Location \"$id\": \"$k\" must be text.");
                }
            }
            $out[$id] = $l;
        }
        return $out;
    }

    private function reachability(array $nodes, array $locations, ?string $start): void
    {
        if (! $nodes) {
            $this->warn('The map has no walkway nodes, so no directions can be given.');
            return;
        }
        // undirected adjacency
        $adj = [];
        foreach ($nodes as $id => $n) {
            $adj[$id] ??= [];
            foreach ($n['connections'] as $c) {
                $adj[$id][] = $c;
                $adj[$c][] = $id;
            }
        }
        $root = null;
        if ($start !== null && ! empty($locations[$start]['node'])) {
            $root = $locations[$start]['node'];
        }
        $root ??= array_key_first($nodes);

        $seen = [$root => true];
        $queue = [$root];
        while ($queue) {
            $cur = array_shift($queue);
            foreach ($adj[$cur] ?? [] as $next) {
                if (! isset($seen[$next])) {
                    $seen[$next] = true;
                    $queue[] = $next;
                }
            }
        }

        $lost = array_keys(array_diff_key($nodes, $seen));
        if ($lost) {
            $this->warn(count($lost) . ' walkway node(s) cannot be reached from the start (' . implode(', ', array_slice($lost, 0, 5)) . (count($lost) > 5 ? ', ...' : '') . '). Guests will get no route to places near them.');
        }
        $stranded = [];
        foreach ($locations as $id => $l) {
            if (! empty($l['node']) && isset($nodes[$l['node']]) && ! isset($seen[$l['node']])) {
                $stranded[] = $l['name'] ?? $id;
            }
        }
        if ($stranded) {
            $this->warn('No walking route to: ' . implode(', ', array_slice($stranded, 0, 8)) . (count($stranded) > 8 ? ', ...' : '') . '.');
        }
    }

    private function floorHasNode(array $nodes, string $floor): bool
    {
        foreach ($nodes as $n) {
            if (($n['floor'] ?? null) === $floor) {
                return true;
            }
        }
        return false;
    }

    private function id(mixed $v): bool
    {
        return is_string($v) && $v !== '' && strlen($v) <= 100;
    }

    private function numbers(array $a, array $keys): bool
    {
        foreach ($keys as $k) {
            if (! isset($a[$k]) || ! is_numeric($a[$k])) {
                return false;
            }
        }
        return true;
    }

    private function err(string $m): void
    {
        if (count($this->errors) < self::MAX_ERRORS) {
            $this->errors[] = $m;
        }
    }

    private function warn(string $m): void
    {
        $this->warnings[] = $m;
    }

    private function result(?string $error = null): array
    {
        if ($error) {
            $this->errors[] = $error;
        }
        return ['errors' => $this->errors, 'warnings' => $this->warnings];
    }
}
