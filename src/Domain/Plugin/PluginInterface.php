<?php

namespace Src\Domain\Plugin;

interface PluginInterface
{
    public function getName(): string;
    public function getVersion(): string;
    public function activate(): void;
    public function deactivate(): void;
}
