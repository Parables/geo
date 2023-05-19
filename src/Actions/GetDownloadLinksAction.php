<?php

namespace Parables\Geo\Actions;

class GetDownloadLinksAction
{
    /**
     * @return string[]]
     */
    public function execute(string $url): array
    {
        $content = file_get_contents($url);
        $content = strip_tags($content, "<a>");

        $result = [];

        $subString = preg_split("/<\/a>/", $content);
        foreach ($subString as $val) {
            if (strpos($val, "<a href=") !== FALSE) {
                $val = preg_replace("/.*<a\s+href=\"/sm", "", $val);
                $val = preg_replace("/\".*/", "", $val);
                $result[] = $val;
            }
        }

        return $result;
    }
}
