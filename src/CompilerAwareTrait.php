<?php

namespace MadeSimple\Database;

trait CompilerAwareTrait
{
    /**
     * @var CompilerInterface
     */
    protected $compiler;

    /**
     * @param CompilerInterface $compiler
     */
    public function setCompiler(CompilerInterface $compiler)
    {
        $this->compiler = $compiler;
    }
}