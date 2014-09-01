<?php
/**
 * @package phpSource
 */
namespace Wsdl2PhpGenerator\PhpSource;

use Wsdl2PhpGenerator\ConfigInterface;

/**
 * Class that represents the source code for a phpdoc comment in php
 *
 * @package phpSource
 * @author Fredrik Wallgren <fredrik.wallgren@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class PhpDocComment
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     *
     * @var PhpDocElement A access element
     * @access private
     */
    private $access;

    /**
     *
     * @var PhpDocElement A var element
     * @access private
     */
    private $var;

    /**
     *
     * @var array Array of PhpDocElements
     * @access private
     */
    private $params;

    /**
     *
     * @var PhpDocElement
     */
    private $return;

    /**
     *
     * @var PhpDocElement
     */
    private $package;

    /**
     *
     * @var PhpDocElement
     */
    private $author;

    /**
     *
     * @var PhpDocElement
     */
    private $licence;

    /**
     *
     * @var array Array of PhpDocElements
     */
    private $throws;

    /**
     *
     * @var string A description in the comment
     */
    private $description;

    /**
     * Constructs the object, sets all variables to empty
     *
     * @param ConfigInterface $config
     * @param string $description
     */
    public function __construct(ConfigInterface $config, $description = '')
    {
        $this->config = $config;
        $this->description = $description;
        $this->access = null;
        $this->var = null;
        $this->params = array();
        $this->throws = array();
        $this->return = null;
        $this->author = null;
        $this->licence = null;
        $this->package = null;
    }

    /**
     * Returns the generated source
     *
     * @return string The sourcecoude of the comment
     * @access public
     */
    public function getSource()
    {
        $ret = PHP_EOL . '/**' . PHP_EOL;

        // TODO: Look over the generation and possible combinations

        $preDescription = trim($this->description);
        if ($this->config->getCommentsDescriptionWithoutGaps()) {
            $preDescription = preg_replace('/([^\s])[ \t]*[\r\n]([ ]{4,}|\t)([^\s])/', '$1 $3', $preDescription);
        }
        $lines = explode(PHP_EOL, $preDescription);
        foreach ($lines as $line) {
            $ret .= ' * ' . trim($line) . PHP_EOL;
        }

        if (strlen($this->description) > 0) {
            $ret .= ' * ' . PHP_EOL;
        }

        // Remove trailing spaces
        $ret = str_replace(' * ' . PHP_EOL, ' *' . PHP_EOL, $ret);

        if (count($this->params) > 0) {
            foreach ($this->params as $param) {
                $ret .= $param->getSource();
            }
        }
        if (count($this->throws) > 0) {
            foreach ($this->throws as $throws) {
                $ret .= $throws->getSource();
            }
        }
        if ($this->var != null) {
            $ret .= $this->var->getSource();
        }
        if ($this->package != null) {
            $ret .= $this->package->getSource();
        }
        if ($this->author != null) {
            $ret .= $this->author->getSource();
        }
        if ($this->access != null) {
            $isPublic      = $this->access->getDatatype() == 'public';
            $withoutPublic = $this->config->getCommentsWithoutPublicAccess();
            if ($isPublic && $withoutPublic) {
                // do nothing
            } else {
                $ret .= $this->access->getSource();
            }
        }
        if ($this->return != null) {
            $ret .= $this->return->getSource();
        }

        $ret .= ' */' . PHP_EOL;

        return $ret;
    }

    /**
     *
     * @param PhpDocElement $access Sets the new access
     */
    public function setAccess(PhpDocElement $access)
    {
        $this->access = $access;
    }

    /**
     *
     * @param PhpDocElement $var Sets the new var
     */
    public function setVar(PhpDocElement $var)
    {
        $this->var = $var;
    }

    /**
     *
     * @param PhpDocElement $package The package element
     */
    public function setPackage(PhpDocElement $package)
    {
        $this->package = $package;
    }

    /**
     *
     * @param PhpDocElement $author The author element
     */
    public function setAuthor(PhpDocElement $author)
    {
        $this->author = $author;
    }

    /**
     *
     * @param PhpDocElement $licence The license elemnt
     */
    public function setLicence(PhpDocElement $licence)
    {
        $this->licence = $licence;
    }

    /**
     *
     * @param PhpDocElement $return Sets the new return
     */
    public function setReturn(PhpDocElement $return)
    {
        $this->return = $return;
    }

    /**
     *
     * @param PhpDocElement $param Adds a new param
     */
    public function addParam(PhpDocElement $param)
    {
        $this->params[] = $param;
    }

    /**
     *
     * @param PhpDocElement $throws Adds a new throws
     */
    public function addThrows(PhpDocElement $throws)
    {
        $this->throws[] = $throws;
    }

    /**
     * Sets the description
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }
}
