<?php

if (! function_exists('versioned_asset')) {
    /**
     * 更新日時をバージョンとして付けたアセットURLを返す。
     *
     * CSS/JSを修正してもブラウザが古いキャッシュを使い続け、
     * 「デプロイしたのにレイアウトが崩れて見える」事故を防ぐ。
     * ファイルを変更するとURLが変わるため、必ず新しい版が読まれる。
     */
    function versioned_asset(string $path): string
    {
        $full = public_path($path);
        $version = is_file($full) ? filemtime($full) : null;

        return asset($path) . ($version ? '?v=' . $version : '');
    }
}
