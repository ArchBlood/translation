<?php

namespace humhub\modules\translation\models\parser;

use HTMLPurifier_AttrDef_Enum;
use HTMLPurifier_AttrDef_Text;
use yii\helpers\HtmlPurifier;

class TranslationPurifier extends HtmlPurifier
{
    /**
     * @inheritDoc
     */
    public static function configure($config)
    {
        // Set HTMLPurifier configuration
        $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
        $config->set('Attr.EnableID', true);

        // Allow specific tags and attributes
        $config->set('HTML.Allowed', 'p,b,i,u,s,a[href|target|title],img[src|alt],ul,ol,li,blockquote,code,pre,span,hr,br,strong');

        // Allow the attribute `target="_blank"` for links
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        // Disable `rel="noreferrer noopener"` because it is automatically added for target blank
        $config->set('HTML.TargetNoopener', false);
        $config->set('HTML.TargetNoreferrer', false);

        // Allow non-ASCII characters
        $config->set('Core.EscapeNonASCIICharacters', false);

        // To avoid escaping inside the attributes
        $def = $config->getHTMLDefinition(true);

        // Apply ParameterURIDef to <a> tag
        $def->addAttribute('a', 'href', new ParameterURIDef());
        $def->addAttribute('a', 'target', new HTMLPurifier_AttrDef_Enum(['_blank', '_self', '_parent', '_top']));
        $def->addAttribute('a', 'title', new HTMLPurifier_AttrDef_Text());
        $def->addAttribute('img', 'src', new ParameterURIDef());
        $def->addAttribute('img', 'alt', new HTMLPurifier_AttrDef_Text());
    }

    /**
     * @inheritdoc
     */
    public static function process($content, $config = null)
    {
        // Keep the char `&` without converting to `&amp;`
        $ampHolder = '_AMP_HOLDER_' . time();
        $content = str_replace('&', $ampHolder, $content);

        $result = parent::process($content, $config);

        return str_replace($ampHolder, '&', $result);
    }
}
