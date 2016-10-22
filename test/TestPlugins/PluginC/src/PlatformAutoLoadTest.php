<?php

namespace PluginC;


class PlatformAutoLoadTest {

    public function getCrossPluginObject()
    {
        return new \PluginA\CrossClass();
    }
}