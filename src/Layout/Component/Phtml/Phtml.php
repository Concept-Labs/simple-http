<?php
namespace Concept\SimpleHttp\Layout\Component\Phtml;

use Closure;
use Concept\SimpleHttp\Layout\Component\AbstractComponent;

class Phtml extends AbstractComponent
{

    public function __toString(): string
    {
        $file = $this->getAbsPath();

        if (!is_file($file)) {
            throw new \RuntimeException(sprintf('Template file "%s" does not exist.', $file));
        }
    
        ob_start();
        ob_implicit_flush(false);

        try {
            include $file;
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        return ob_get_clean() ?: '';

        //$this->getStream()->write(ob_get_clean() ?: '');
        
    }

    /**
     * Get the absolute path to the template file.
     *
     * @return string
     */
    protected function getAbsPath(): string
    {

        return sprintf(
            '%s%s%s',
            rtrim($this->getConfig()->get('resources') ?? '', DIRECTORY_SEPARATOR),
            DIRECTORY_SEPARATOR,
            ltrim($this->getTemplate(), DIRECTORY_SEPARATOR)
        );
    }

    /**
     * Get the template file name from config
     *
     * @return string
     * @throws \LogicException if template is not set in config
     */
    protected function getTemplate(): string
    {
        $template = $this->getConfig()->get('template');

        if (null === $template) {
            throw new \LogicException('Template is not set in config.');
        }

        return $template;
    }
}