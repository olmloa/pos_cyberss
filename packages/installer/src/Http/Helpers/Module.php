<?php

use Tecdiary\Installer\Install;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Modules\Shop\Console\Commands\ShopSetting;

// read modules file
if (! function_exists('get_module')) {
    function get_module($name = null)
    {
        if (env('APP_INSTALLED', false) == true) {
            $modules = cache()->rememberForever('sma_modules', function () {
                return json_decode(Storage::disk('local')->get('modules.json'), true);
            });
        } else {
            $modules = json_decode(Storage::disk('local')->get('modules.json'), true);
        }

        if ($name) {
            return $modules[$name]['enabled'] ?? null;
        }

        return $modules;
    }
}

// write modules file
if (! function_exists('write_module')) {
    function write_module(array $data)
    {
        if (! ($data['sma'] ?? null)) {
            throw new Exception('Please provide sma key.');
        }

        $data['pos'] ??= ['key' => null, 'enabled' => false];
        $data['shop'] ??= ['key' => null, 'enabled' => false];

        cache()->forget('sma_modules');

        return Storage::disk('local')->put('modules.json', json_encode($data, JSON_PRETTY_PRINT));
    }
}

// disable module
if (! function_exists('disable_module')) {
    function disable_module($name)
    {
        if (! in_array($name, ['pos', 'shop'])) {
            throw new Exception('No module found with name ' . $name);
        }

        $modules = get_module();
        $modules[$name]['enabled'] = false;

        return write_module($modules);
    }
}

// enable module
if (! function_exists('enable_module')) {
    function enable_module($name, $key)
    {
        if (! $key) {
            throw new Exception('Please provide a valid key.');
        }

        if (! in_array($name, ['pos', 'shop'])) {
            throw new Exception('No module found with name ' . $name);
        }

        $res = Install::verifyPurchase($key);
        if ($res['error'] ?? null) {
            throw new Exception($res['description'] ?? 'Failed to verify purchase code!');
        }

        if ($res['purchase-data'] ?? null) {
            $module_id = match ($name) {
                'shop' => ['20046278', '23045302'],
                'pos'  => ['4494018', '5403161', '23045302'],
            };

            if (! in_array($res['purchase-data']['item_id'], $module_id)) {
                throw new Exception('Purchase code does not belongs to the selected module!');
            }
        }

        if ($name == 'shop' && ! File::exists(base_path('modules/Shop'))) {
            throw new Exception('Shop module files are missing! Please upload the module files first.');
        }

        if ($name == 'pos' && (! File::exists(base_path('app/Http/Controllers/Sma/Pos')) && ! File::exists(base_path('app/Models/Sma/Pos')))) {
            throw new Exception('POS module files are missing! Please upload the module files first.');
        }

        $modules = get_module();
        $modules[$name]['key'] = $key;
        $modules[$name]['enabled'] = true;

        if ($name == 'shop') {
            Artisan::call(ShopSetting::class);
        }

        return write_module($modules);
    }
}

// install module
if (! function_exists('install_module')) {
    function install_module($name, $key)
    {
        if (! in_array($name, ['pos', 'shop'])) {
            throw new Exception('No module found with name ' . $name);
        }

        $user = auth()->user();
        if ($user && $user->hasRole('Super Admin')) {
            try {
                $e = Artisan::call("sma:install-module {$name} {$key}");

                if ($e === 0) {
                    return enable_module($name, $key);
                }

                throw new Exception(Artisan::output());
            } catch (Exception $e) {
                return $e->getMessage();
            }
        }

        return false;
    }
}
