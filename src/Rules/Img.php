<?php

namespace NdB\TwigCSA11Y\Rules;

use FriendsOfTwig\Twigcs\Rule\AbstractRule;
use FriendsOfTwig\Twigcs\Rule\RuleInterface;
use FriendsOfTwig\Twigcs\TwigPort\Token as TwigToken;
use FriendsOfTwig\Twigcs\TwigPort\TokenStream;

class Img extends AbstractRule implements RuleInterface
{
    /**
     * @var \FriendsOfTwig\Twigcs\Validator\Violation[]
     */
    protected $violations = [];

    /**
     * @param int $severity
     */
    public function __construct($severity)
    {
        parent::__construct($severity);
        $this->violations = [];
    }

    public function check(TokenStream $tokens)
    {
        $violations = [];

        while (!$tokens->isEOF()) {
            $token = $tokens->getCurrent();

            if ($token->getType() === TwigToken::TEXT_TYPE) {
                $matches = [];
                $textToAnalyse = (string) $token->getValue();
                $terminated  = false;
                $tokenIndex = 1;
                while (!$terminated) {
                    $nextToken = $tokens->look($tokenIndex);
                    if ($nextToken->getType() !== TwigToken::ARROW_TYPE) {
                        $textToAnalyse .= (string) $nextToken->getValue();
                    }
                    if ($nextToken->getType() === TwigToken::TEXT_TYPE
                        || $nextToken->getType() === TwigToken::EOF_TYPE
                    ) {
                        $terminated = true;
                    }
                    $tokenIndex++;
                }

                if (preg_match(
                    '/<(img|i)\s+[^>]*(alt\s*=\s*["\']([^"\']*)["\']|aria-label\s*=\s*["\']([^"\']*)["\'])[^>]*>/i',
                    $textToAnalyse,
                    $matches
                )
                ) {
	                $tagName = $matches[1];
	                $altValue = $matches[4] ?? $matches[6] ?? '';
	                if (empty($altValue)) {
		                /**
		                 * @psalm-suppress InternalMethod
		                 * @psalm-suppress UndefinedPropertyFetch
		                 */
		                $violations[] = $this->createViolation(
			                (string) $tokens->getSourceContext()->getPath(),
			                $token->getLine(),
			                $token->getColumn(),
			                sprintf(
				                '[Weglot.Img] Invalid \'Img alt\'. Img must have an non empty alt or aria-label attribute.'
			                )
		                );
	                }
                }else{
	                if (!preg_match(
		                '/<(img|i)\s+[^>]*(aria-hidden\s*=\s*["\']true["\']|role\s*=\s*["\']presentation["\']|alt\s*=\s*["\']""["\'])[^>]*>/i',
		                $textToAnalyse,
		                $matches
	                )
	                ) {
		                $tagName = $matches[1];
		                $attributeValue = $matches[4] ?? $matches[6] ?? $matches[8] ?? '';

		                if (empty($attributeValue)) {
			                /**
			                 * @psalm-suppress InternalMethod
			                 * @psalm-suppress UndefinedPropertyFetch
			                 */
			                $violations[] = $this->createViolation(
				                (string) $tokens->getSourceContext()->getPath(),
				                $token->getLine(),
				                $token->getColumn(),
				                sprintf(
					                '[Weglot.Img] Invalid \'Img alt\'. Img with no description should have a aria-hidden="true" attribute or role="presentation" or alt=""'
				                )
			                );
		                }
	                }

                }
            }
            $tokens->next();
        }
        return $violations;
    }
}
