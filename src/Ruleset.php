<?php

namespace NdB\TwigCSA11Y;

use FriendsOfTwig\Twigcs\Ruleset\RulesetInterface;
use FriendsOfTwig\Twigcs\Validator\Violation;
use NdB\TwigCSA11Y\Rules\AriaRoles;
use NdB\TwigCSA11Y\Rules\BannedHTMLTags;
use NdB\TwigCSA11Y\Rules\Iframe;
use NdB\TwigCSA11Y\Rules\Img;
use NdB\TwigCSA11Y\Rules\Link;
use NdB\TwigCSA11Y\Rules\TabIndex;

class Ruleset implements RulesetInterface
{
    private $twigMajorVersion;

    public function __construct(int $twigMajorVersion)
    {
        $this->twigMajorVersion = $twigMajorVersion;
    }

	public function getRules()
	{
		return [
			new BannedHTMLTags(Violation::SEVERITY_ERROR),
			new TabIndex(Violation::SEVERITY_ERROR),
			new AriaRoles(Violation::SEVERITY_ERROR),
			new Link(Violation::SEVERITY_ERROR),
			new Iframe(Violation::SEVERITY_ERROR),
			new Img(Violation::SEVERITY_ERROR)
		];
	}
}
