<?php

namespace Mehedi\Stylist\Theme;

use Illuminate\Routing\UrlGenerator as RoutingUrlGenerator;

class UrlGenerator extends RoutingUrlGenerator
{
    /**
     * Generate a URL to an application asset.
     *
     * @param  string  $path
     * @param  bool|null  $secure
     * @return string
     */

    public function asset($path, $secure = null)
    {
        if (strpos($path, '://') || strpos($path, '//') === 0) {
            return $path;
        }

        // If the path is already a valid URL we will not try to generate a new one
        if ($this->isValidUrl($path)) {
            return $path;
        }

        $root = $this->getRootUrl($this->getScheme($secure));

        $theme = Stylist::getcurrentTheme();

        return $this->removeIndex($root).'/themes/'.$theme->getPath().'/'.trim($path, '/');
    }
}
