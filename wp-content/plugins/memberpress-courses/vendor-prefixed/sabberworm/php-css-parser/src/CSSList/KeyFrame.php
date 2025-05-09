<?php
/**
 * @license MIT
 *
 * Modified by Team Caseproof using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace memberpress\courses\Sabberworm\CSS\CSSList;

use memberpress\courses\Sabberworm\CSS\OutputFormat;
use memberpress\courses\Sabberworm\CSS\Property\AtRule;

class KeyFrame extends CSSList implements AtRule
{
    /**
     * @var string|null
     */
    private $vendorKeyFrame;

    /**
     * @var string|null
     */
    private $animationName;

    /**
     * @param int $iLineNo
     */
    public function __construct($iLineNo = 0)
    {
        parent::__construct($iLineNo);
        $this->vendorKeyFrame = null;
        $this->animationName = null;
    }

    /**
     * @param string $vendorKeyFrame
     */
    public function setVendorKeyFrame($vendorKeyFrame)
    {
        $this->vendorKeyFrame = $vendorKeyFrame;
    }

    /**
     * @return string|null
     */
    public function getVendorKeyFrame()
    {
        return $this->vendorKeyFrame;
    }

    /**
     * @param string $animationName
     */
    public function setAnimationName($animationName)
    {
        $this->animationName = $animationName;
    }

    /**
     * @return string|null
     */
    public function getAnimationName()
    {
        return $this->animationName;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->render(new OutputFormat());
    }

    /**
     * @param OutputFormat|null $oOutputFormat
     *
     * @return string
     */
    public function render($oOutputFormat)
    {
        $sResult = $oOutputFormat->comments($this);
        $sResult .= "@{$this->vendorKeyFrame} {$this->animationName}{$oOutputFormat->spaceBeforeOpeningBrace()}{";
        $sResult .= $this->renderListContents($oOutputFormat);
        $sResult .= '}';
        return $sResult;
    }

    /**
     * @return bool
     */
    public function isRootList()
    {
        return false;
    }

    /**
     * @return string|null
     */
    public function atRuleName()
    {
        return $this->vendorKeyFrame;
    }

    /**
     * @return string|null
     */
    public function atRuleArgs()
    {
        return $this->animationName;
    }
}
