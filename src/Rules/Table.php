<?php

namespace NdB\TwigCSA11Y\Rules;

use FriendsOfTwig\Twigcs\Rule\AbstractRule;
use FriendsOfTwig\Twigcs\Rule\RuleInterface;
use FriendsOfTwig\Twigcs\TwigPort\Token as TwigToken;
use FriendsOfTwig\Twigcs\TwigPort\TokenStream;

class Table extends AbstractRule implements RuleInterface
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
                if (!preg_match(
                    '/<th(?=\s)[^>]*\srole\s*=\s*["\']\s*columnheader\s*["\'][^>]*>/i',
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
                            '[A11Y.Table] Invalid \'column header role\'. All column headers should have the WAI-ARIA role: “columnheader”',
                        )
                    );
                }
            }

            $tokens->next();
        }
        return $violations;
    }
}
