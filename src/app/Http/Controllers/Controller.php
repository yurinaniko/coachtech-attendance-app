<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * URL・クエリの日付/月を厳密な形式で読む。不正な値は 404 にする。
     *
     * Carbon::parse は "yesterday" や "abc" も受け付け、失敗時は例外で 500 になる。
     * 2026-02-30 のような存在しない日も繰り上げて通すため、書式で往復して一致を確かめる。
     */
    protected function parseDateOrFail(?string $value, string $format): Carbon
    {
        try {
            $date = Carbon::createFromFormat('!' . $format, (string) $value);
        } catch (\Throwable) {
            abort(404);
        }

        abort_unless($date->format($format) === $value, 404);

        return $date;
    }
}
