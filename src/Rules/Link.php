<?php

namespace NdB\TwigCSA11Y\Rules;

use FriendsOfTwig\Twigcs\Rule\AbstractRule;
use FriendsOfTwig\Twigcs\Rule\RuleInterface;
use FriendsOfTwig\Twigcs\TwigPort\Token as TwigToken;
use FriendsOfTwig\Twigcs\TwigPort\TokenStream;

class Link extends AbstractRule implements RuleInterface
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
                    '/<a[^>]*\s+target="_blank"[^>]*>.*open_in_new<\/i>/i',
                    $textToAnalyse,
                    $matches
                )
                ) {
                    /**
                     * @psalm-suppress InternalMethod
                     * @psalm-suppress UndefinedPropertyFetch
                     */
                    $violations[] = $this->createViolation(
                        (string) $tokens->getSourceContext()->getPath(),
                        $token->getLine(),
                        $token->getColumn(),
                        sprintf(
                            '[Weglot.LinkBlank] Invalid \'link blank\'. Link with target blank must have the icon. Found `%1$s`.',
                            trim($matches[0])
                        )
                    );
                }

	            if (preg_match(
		            '/<a[^>]*\s+title="[^"]*"/i',
		            $textToAnalyse,
		            $matches
	            )
	            ) {
		            /**
		             * @psalm-suppress InternalMethod
		             * @psalm-suppress UndefinedPropertyFetch
		             */
		            $violations[] = $this->createViolation(
			            (string) $tokens->getSourceContext()->getPath(),
			            $token->getLine(),
			            $token->getColumn(),
			            sprintf(
				            '[Weglot.LinkTitle] Invalid \'link title\'. Link must have a title attribute. Found `%1$s`.',
				            trim($matches[0])
			            )
		            );
	            }
            }

            $tokens->next();
        }
        return $violations;
    }
}
