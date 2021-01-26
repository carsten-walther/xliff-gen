<?php

namespace CarstenWalther\XliffGen\Parser;

use CarstenWalther\XliffGen\Parser\SyntaxTree\ArrayNode;
use CarstenWalther\XliffGen\Parser\SyntaxTree\BooleanNode;
use CarstenWalther\XliffGen\Parser\SyntaxTree\ObjectAccessorNode;
use CarstenWalther\XliffGen\Parser\SyntaxTree\RootNode;
use CarstenWalther\XliffGen\Parser\SyntaxTree\TextNode;
use CarstenWalther\XliffGen\Parser\SyntaxTree\ViewHelperNode;
use CarstenWalther\XliffGen\Utility\Debug;

/**
 * Class Parser
 *
 * @package CarstenWalther\XliffGen\Parser
 */
class Parser
{
    public static $SCAN_PATTERN_NAMESPACEDECLARATION = '/(?<!\\\\){namespace\s*(?P<identifier>[a-zA-Z]+[a-zA-Z0-9]*)\s*=\s*(?P<phpNamespace>(?:[A-Za-z0-9\.]+|Tx)(?:\\\\\w+)+)\s*}/m';
    public static $SCAN_PATTERN_XMLNSDECLARATION = '/\sxmlns:(?P<identifier>.*?)="(?P<xmlNamespace>.*?)"/m';

    /**
     * The following two constants are used for tracking whether we are currently
     * parsing ViewHelper arguments or not. This is used to parse arrays only as
     * ViewHelper argument.
     */
    public const CONTEXT_INSIDE_VIEWHELPER_ARGUMENTS = 1;
    public const CONTEXT_OUTSIDE_VIEWHELPER_ARGUMENTS = 2;

    /**
     * This regular expression splits the input string at all dynamic tags, AND
     * on all <![CDATA[...]]> sections.
     *
     */
    public static $SPLIT_PATTERN_TEMPLATE_DYNAMICTAGS = '/
        (
            (?: <\/?                                      # Start dynamic tags
                    (?:(?:NAMESPACE):[a-zA-Z0-9\\.]+)     # A tag consists of the namespace prefix and word characters
                    (?:                                   # Begin tag arguments
                        \s*[a-zA-Z0-9:-]+                  # Argument Keys
                        =                                 # =
                        (?>                               # either... If we have found an argument, we will not back-track (That does the Atomic Bracket)
                            "(?:\\\"|[^"])*"              # a double-quoted string
                            |\'(?:\\\\\'|[^\'])*\'        # or a single quoted string
                        )\s*                              #
                    )*                                    # Tag arguments can be replaced many times.
                \s*
                \/?>                                      # Closing tag
            )
            |(?:                                          # Start match CDATA section
                <!\[CDATA\[.*?\]\]>
            )
        )/xs';

    /**
     * This regular expression scans if the input string is a ViewHelper tag
     *
     */
    public static $SCAN_PATTERN_TEMPLATE_VIEWHELPERTAG = '/
        ^<                                                # A Tag begins with <
        (?P<NamespaceIdentifier>NAMESPACE):               # Then comes the Namespace prefix followed by a :
        (?P<MethodIdentifier>                             # Now comes the Name of the ViewHelper
            [a-zA-Z0-9\\.]+
        )
        (?P<Attributes>                                   # Begin Tag Attributes
            (?:                                           # A tag might have multiple attributes
                \s*
                [a-zA-Z0-9:-]+                             # The attribute name
                =                                         # =
                (?>                                       # either... # If we have found an argument, we will not back-track (That does the Atomic Bracket)
                    "(?:\\\"|[^"])*"                      # a double-quoted string
                    |\'(?:\\\\\'|[^\'])*\'                # or a single quoted string
                )                                         #
                \s*
            )*                                            # A tag might have multiple attributes
        )                                                 # End Tag Attributes
        \s*
        (?P<Selfclosing>\/?)                              # A tag might be selfclosing
        >$/x';

    /**
     * This regular expression scans if the input string is a closing ViewHelper
     * tag.
     *
     */
    public static $SCAN_PATTERN_TEMPLATE_CLOSINGVIEWHELPERTAG = '/^<\/(?P<NamespaceIdentifier>NAMESPACE):(?P<MethodIdentifier>[a-zA-Z0-9\\.]+)\s*>$/';

    /**
     * This regular expression splits the tag arguments into its parts
     *
     */
    public static $SPLIT_PATTERN_TAGARGUMENTS = '/
        (?:                                              #
            \s*                                          #
            (?P<Argument>                                # The attribute name
                [a-zA-Z0-9:-]+                            #
            )                                            #
            =                                            # =
            (?>                                          # If we have found an argument, we will not back-track (That does the Atomic Bracket)
                (?P<ValueQuoted>                         # either...
                    (?:"(?:\\\"|[^"])*")                 # a double-quoted string
                    |(?:\'(?:\\\\\'|[^\'])*\')           # or a single quoted string
                )
            )\s*
        )
        /xs';

    /**
     * This pattern detects CDATA sections and outputs the text between opening
     * and closing CDATA.
     *
     */
    public static $SCAN_PATTERN_CDATA = '/^<!\[CDATA\[(.*?)\]\]>$/s';

    /**
     * Pattern which splits the shorthand syntax into different tokens. The
     * "shorthand syntax" is everything like {...}
     *
     */
    public static $SPLIT_PATTERN_SHORTHANDSYNTAX = '/
        (
            {                                # Start of shorthand syntax
                (?:                          # Shorthand syntax is either composed of...
                    [a-zA-Z0-9\->_:,.()]     # Various characters
                    |"(?:\\\"|[^"])*"        # Double-quoted strings
                    |\'(?:\\\\\'|[^\'])*\'   # Single-quoted strings
                    |(?R)                    # Other shorthand syntaxes inside, albeit not in a quoted string
                    |\s+                     # Spaces
                )+
            }                                # End of shorthand syntax
        )/x';

    /**
     * Pattern which detects the object accessor syntax:
     * {object.some.value}, additionally it detects ViewHelpers like
     * {f:for(param1:bla)} and chaining like
     * {object.some.value->f:bla.blubb()->f:bla.blubb2()}
     *
     * THIS IS ALMOST THE SAME AS IN $SCAN_PATTERN_SHORTHANDSYNTAX_ARRAYS
     *
     */
    public static $SCAN_PATTERN_SHORTHANDSYNTAX_OBJECTACCESSORS = '/
        ^{                                                      # Start of shorthand syntax
                                                            # A shorthand syntax is either...
            (?P<Object>[a-zA-Z0-9\-_.]*)                                     # ... an object accessor
            \s*(?P<Delimiter>(?:->)?)\s*

            (?P<ViewHelper>                                 # ... a ViewHelper
                [a-zA-Z0-9]+                                # Namespace prefix of ViewHelper (as in $SCAN_PATTERN_TEMPLATE_VIEWHELPERTAG)
                :
                [a-zA-Z0-9\\.]+                             # Method Identifier (as in $SCAN_PATTERN_TEMPLATE_VIEWHELPERTAG)
                \(                                          # Opening parameter brackets of ViewHelper
                    (?P<ViewHelperArguments>                # Start submatch for ViewHelper arguments. This is taken from $SCAN_PATTERN_SHORTHANDSYNTAX_ARRAYS
                        (?:
                            \s*[a-zA-Z0-9\-_]+                  # The keys of the array
                            \s*:\s*                             # Key|Value delimiter :
                            (?:                                 # Possible value options:
                                "(?:\\\"|[^"])*"                # Double qouoted string
                                |\'(?:\\\\\'|[^\'])*\'          # Single quoted string
                                |[a-zA-Z0-9\-_.]+               # variable identifiers
                                |{(?P>ViewHelperArguments)}     # Another sub-array
                            )                                   # END possible value options
                            \s*,?                               # There might be a , to seperate different parts of the array
                        )*                                  # The above cycle is repeated for all array elements
                    )                                       # End ViewHelper Arguments submatch
                \)                                          # Closing parameter brackets of ViewHelper
            )?
            (?P<AdditionalViewHelpers>                      # There can be more than one ViewHelper chained, by adding more -> and the ViewHelper (recursively)
                (?:
                    \s*->\s*
                    (?P>ViewHelper)
                )*
            )
        }$/x';

    /**
     * THIS IS ALMOST THE SAME AS $SCAN_PATTERN_SHORTHANDSYNTAX_OBJECTACCESSORS
     *
     */
    public static $SPLIT_PATTERN_SHORTHANDSYNTAX_VIEWHELPER = '/

        (?P<NamespaceIdentifier>[a-zA-Z0-9]+)       # Namespace prefix of ViewHelper (as in $SCAN_PATTERN_TEMPLATE_VIEWHELPERTAG)
        :
        (?P<MethodIdentifier>[a-zA-Z0-9\\.]+)
        \(                                          # Opening parameter brackets of ViewHelper
            (?P<ViewHelperArguments>                # Start submatch for ViewHelper arguments. This is taken from $SCAN_PATTERN_SHORTHANDSYNTAX_ARRAYS
                (?:
                    \s*[a-zA-Z0-9\-_]+                  # The keys of the array
                    \s*:\s*                             # Key|Value delimiter :
                    (?:                                 # Possible value options:
                        "(?:\\\"|[^"])*"                # Double qouoted string
                        |\'(?:\\\\\'|[^\'])*\'          # Single quoted string
                        |[a-zA-Z0-9\-_.]+               # variable identifiers
                        |{(?P>ViewHelperArguments)}     # Another sub-array
                    )                                   # END possible value options
                    \s*,?                               # There might be a , to seperate different parts of the array
                )*                                  # The above cycle is repeated for all array elements
            )                                       # End ViewHelper Arguments submatch
        \)                                          # Closing parameter brackets of ViewHelper
        /x';

    /**
     * Pattern which detects the array/object syntax like in JavaScript, so it
     * detects strings like:
     * {object: value, object2: {nested: array}, object3: "Some string"}
     *
     * THIS IS ALMOST THE SAME AS IN SCAN_PATTERN_SHORTHANDSYNTAX_OBJECTACCESSORS
     *
     */
    public static $SCAN_PATTERN_SHORTHANDSYNTAX_ARRAYS = '/^
        (?P<Recursion>                                  # Start the recursive part of the regular expression - describing the array syntax
            {                                           # Each array needs to start with {
                (?P<Array>                              # Start sub-match
                    (?:
                        \s*[a-zA-Z0-9\-_]+              # The keys of the array
                        \s*:\s*                         # Key|Value delimiter :
                        (?:                             # Possible value options:
                            "(?:\\\"|[^"])*"            # Double quoted string
                            |\'(?:\\\\\'|[^\'])*\'      # Single quoted string
                            |[a-zA-Z0-9\-_.]+           # variable identifiers
                            |(?P>Recursion)             # Another sub-array
                        )                               # END possible value options
                        \s*,?                           # There might be a , to separate different parts of the array
                    )*                                  # The above cycle is repeated for all array elements
                )                                       # End array sub-match
            }                                           # Each array ends with }
        )$/x';

    /**
     * This pattern splits an array into its parts. It is quite similar to the
     * pattern above.
     *
     */
    public static $SPLIT_PATTERN_SHORTHANDSYNTAX_ARRAY_PARTS = '/
        (?P<ArrayPart>                                             # Start sub-match
            (?P<Key>[a-zA-Z0-9\-_]+)                               # The keys of the array
            \s*:\s*                                                   # Key|Value delimiter :
            (?:                                                       # Possible value options:
                (?P<QuotedString>                                     # Quoted string
                    (?:"(?:\\\"|[^"])*")
                    |(?:\'(?:\\\\\'|[^\'])*\')
                )
                |(?P<VariableIdentifier>[a-zA-Z][a-zA-Z0-9\-_.]*)    # variable identifiers have to start with a letter
                |(?P<Number>[0-9.]+)                                  # Number
                |{\s*(?P<Subarray>(?:(?P>ArrayPart)\s*,?\s*)+)\s*}              # Another sub-array
            )                                                         # END possible value options
        )                                                          # End array part sub-match
    /x';

    /**
     * This pattern detects the default xml namespace
     *
     */
    public static $SCAN_PATTERN_DEFAULT_XML_NAMESPACE = '/^http\:\/\/typo3\.org\/ns\/(?P<PhpNamespace>.+)$/s';

    /**
     * Namespace identifiers and their component name prefix (Associative array).
     *
     * @var array
     */
    protected $namespaces = [
        'f' => 'TYPO3\Fluid\ViewHelpers'
    ];

    /**
     * @var array
     */
    protected $stack = [];

    /**
     * Parses a given template string and returns a parsed template object.
     *
     * The resulting ParsedTemplate can then be rendered by calling evaluate() on it.
     *
     * Normally, you should use a subclass of AbstractTemplateView instead of calling the
     * TemplateParser directly.
     *
     * @param string $templateString The template to parse as a string
     *
     * @return \CarstenWalther\XliffGen\Parser\ParsingState
     * @throws \Exception
     */
    public function parse(string $templateString) : ParsingState
    {
        if (!is_string($templateString)) {
            throw new \Exception('Parser requires a template string as argument, ' . gettype($templateString) . ' given.');
        }

        $this->reset();

        $templateString = $this->extractNamespaceDefinitions($templateString);
        $splitTemplate = $this->splitTemplateAtDynamicTags($templateString);
        return $this->buildObjectTree($splitTemplate, self::CONTEXT_OUTSIDE_VIEWHELPER_ARGUMENTS);
    }

    /**
     * Resets the parser to its default values.
     *
     * @return void
     */
    protected function reset() : void
    {
        $this->namespaces = [
            'f' => 'TYPO3\Fluid\ViewHelpers'
        ];
    }

    /**
     * Extracts namespace definitions out of the given template string and sets
     * $this->namespaces.
     *
     * @param string $templateString Template string to extract the namespaces from
     *
     * @return string The updated template string without namespace declarations inside
     * @throws \Exception if a namespace can't be resolved or has been declared already
     */
    protected function extractNamespaceDefinitions(string $templateString) : string
    {
        $matches = [];
        preg_match_all(self::$SCAN_PATTERN_XMLNSDECLARATION, $templateString, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            // skip reserved "f" namespace identifier
            if ($match['identifier'] === 'f') {
                continue;
            }
            if (array_key_exists($match['identifier'], $this->namespaces)) {
                throw new \Exception(sprintf('Namespace identifier "%s" is already registered. Do not re-declare namespaces!', $match['identifier']));
            }
            $matchedPhpNamespace = [];
            if (preg_match(self::$SCAN_PATTERN_DEFAULT_XML_NAMESPACE, $match['xmlNamespace'], $matchedPhpNamespace) === 0) {
                continue;
            }
            $phpNamespace = str_replace('/', '\\', $matchedPhpNamespace['PhpNamespace']);
            $this->namespaces[$match['identifier']] = $phpNamespace;
        }

        $matches = [];
        preg_match_all(self::$SCAN_PATTERN_NAMESPACEDECLARATION, $templateString, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            if (array_key_exists($match['identifier'], $this->namespaces)) {
                throw new \Exception(sprintf('Namespace identifier "%s" is already registered. Do not re-declare namespaces!', $match['identifier']));
            }
            $this->namespaces[$match['identifier']] = $match['phpNamespace'];
        }

        if ($matches !== []) {
            $templateString = preg_replace(self::$SCAN_PATTERN_NAMESPACEDECLARATION, '', $templateString);
        }

        return $templateString;
    }

    /**
     * Splits the template string on all dynamic tags found.
     *
     * @param string $templateString Template string to split.
     *
     * @return array Splitted template
     */
    protected function splitTemplateAtDynamicTags(string $templateString) : array
    {
        $regularExpression = $this->prepareTemplateRegularExpression(self::$SPLIT_PATTERN_TEMPLATE_DYNAMICTAGS);
        return preg_split($regularExpression, $templateString, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    }

    /**
     * Build object tree from the split template
     *
     * @param array   $splitTemplate The split template, so that every tag with a namespace declaration is already a seperate array element.
     * @param integer $context       one of the CONTEXT_* constants, defining whether we are inside or outside of ViewHelper arguments currently.
     *
     * @return \CarstenWalther\XliffGen\Parser\ParsingState
     * @throws \Exception
     */
    protected function buildObjectTree(array $splitTemplate, int $context) : ParsingState
    {
        $regularExpression_openingViewHelperTag = $this->prepareTemplateRegularExpression(self::$SCAN_PATTERN_TEMPLATE_VIEWHELPERTAG);
        $regularExpression_closingViewHelperTag = $this->prepareTemplateRegularExpression(self::$SCAN_PATTERN_TEMPLATE_CLOSINGVIEWHELPERTAG);

        /** @var $state ParsingState */
        $state = new ParsingState();

        /** @var $rootNode RootNode */
        $rootNode = new RootNode();

        $state->setRootNode($rootNode);
        $state->pushNodeToStack($rootNode);

        foreach ($splitTemplate as $templateElement) {
            $matchedVariables = [];
            if (preg_match(self::$SCAN_PATTERN_CDATA, $templateElement, $matchedVariables) > 0) {
                $this->textHandler($state, $matchedVariables[1]);
            } elseif (preg_match($regularExpression_openingViewHelperTag, $templateElement, $matchedVariables) > 0) {
                $this->openingViewHelperTagHandler($state, $matchedVariables['NamespaceIdentifier'], $matchedVariables['MethodIdentifier'], $matchedVariables['Attributes'], ($matchedVariables['Selfclosing'] !== ''));
            } elseif (preg_match($regularExpression_closingViewHelperTag, $templateElement, $matchedVariables) > 0) {
                $this->closingViewHelperTagHandler($state, $matchedVariables['NamespaceIdentifier'], $matchedVariables['MethodIdentifier']);
            } else {
                $this->textAndShorthandSyntaxHandler($state, $templateElement, $context);
            }
        }

        if ($state->countNodeStack() !== 1) {
            throw new \Exception('Not all tags were closed!', 1238169398);
        }

        return $state;
    }

    /**
     * Removes escapings from a given argument string and trims the outermost
     * quotes.
     *
     * This method is meant as a helper for regular expression results.
     *
     * @param string $quotedValue Value to unquote
     *
     * @return string Unquoted value
     */
    protected function unquoteString(string $quotedValue) : string
    {
        switch ($quotedValue[0]) {
            case '"':
                $value = str_replace('\\"', '"', preg_replace('/(^"|"$)/', '', $quotedValue));
                break;
            case "'":
                $value = str_replace("\\'", "'", preg_replace('/(^\'|\'$)/', '', $quotedValue));
                break;
            default:
                $value = $quotedValue;
        }
        return str_replace('\\\\', '\\', $value);
    }

    /**
     * Parse arguments of a given tag, and build up the Arguments Object Tree
     * for each argument.
     * Returns an associative array, where the key is the name of the argument,
     * and the value is a single Argument Object Tree.
     *
     * @param string $argumentsString All arguments as string
     *
     * @return array An associative array of objects, where the key is the argument name.
     * @throws \Exception
     */
    protected function parseArguments(string $argumentsString) : array
    {
        $argumentsObjectTree = [];
        $matches = [];
        if (preg_match_all(self::$SPLIT_PATTERN_TAGARGUMENTS, $argumentsString, $matches, PREG_SET_ORDER) > 0) {
            foreach ($matches as $singleMatch) {
                $argument = $singleMatch['Argument'];
                $value = $this->unquoteString($singleMatch['ValueQuoted']);
                $argumentsObjectTree[$argument] = $this->buildArgumentObjectTree($value);
            }
        }
        return $argumentsObjectTree;
    }

    /**
     * Build up an argument object tree for the string in $argumentString.
     * This builds up the tree for a single argument value.
     *
     * This method also does some performance optimizations, so in case
     * no { or < is found, then we just return a TextNode.
     *
     * @param string $argumentString
     *
     * @return \CarstenWalther\XliffGen\Parser\SyntaxTree\AbstractNode
     * @throws \Exception
     */
    protected function buildArgumentObjectTree(string $argumentString)
    {
        if (strpos($argumentString, '{') === false && strpos($argumentString, '<') === false) {
            return new TextNode($argumentString);
        }
        $splitArgument = $this->splitTemplateAtDynamicTags($argumentString);
        return $this->buildObjectTree($splitArgument, self::CONTEXT_INSIDE_VIEWHELPER_ARGUMENTS)->getRootNode();
    }

    /**
     * Takes a regular expression template and replaces "NAMESPACE" with the
     * currently registered namespace identifiers. Returns a regular expression
     * which is ready to use.
     *
     * @param string $regularExpression Regular expression template
     *
     * @return string Regular expression ready to be used
     */
    protected function prepareTemplateRegularExpression(string $regularExpression) : string
    {
        return str_replace('NAMESPACE', implode('|', array_keys($this->namespaces)), $regularExpression);
    }

    /**
     * Text node handler
     *
     * @param \CarstenWalther\XliffGen\Parser\ParsingState $state
     * @param string                                       $text
     *
     * @return void
     * @throws \Exception
     */
    protected function textHandler(ParsingState $state, string $text) : void
    {
        /** @var $node TextNode */
        $node = new TextNode($text);
        $state->getNodeFromStack()->addChildNode($node);
    }

    /**
     * Handles an opening or self-closing view helper tag.
     *
     * @param \CarstenWalther\XliffGen\Parser\ParsingState $state
     * @param string                                       $namespaceIdentifier Namespace identifier - being looked up in $this->namespaces
     * @param string                                       $methodIdentifier    Method identifier
     * @param string                                       $arguments           Arguments string, not yet parsed
     * @param boolean                                      $selfclosing         true, if the tag is a self-closing tag.
     *
     * @return void
     * @throws \Exception
     */
    protected function openingViewHelperTagHandler(ParsingState $state, string $namespaceIdentifier, string $methodIdentifier, string $arguments, bool $selfclosing) : void
    {
        $argumentsObjectTree = $this->parseArguments($arguments);
        $this->initializeViewHelperAndAddItToStack($state, $namespaceIdentifier, $methodIdentifier, $argumentsObjectTree);

        if ($selfclosing) {
            $node = $state->popNodeFromStack();
        }
    }

    /**
     * Handles a closing view helper tag
     *
     * @param \CarstenWalther\XliffGen\Parser\ParsingState $state
     * @param string                                       $namespaceIdentifier Namespace identifier for the closing tag.
     * @param string                                       $methodIdentifier    Method identifier.
     *
     * @return void
     * @throws \Exception
     */
    protected function closingViewHelperTagHandler(ParsingState $state, string $namespaceIdentifier, string $methodIdentifier) : void
    {
        if (!array_key_exists($namespaceIdentifier, $this->namespaces)) {
            throw new \Exception('Namespace could not be resolved. This exception should never be thrown!');
        }
        $lastStackElement = $state->popNodeFromStack();
        if (!($lastStackElement instanceof ViewHelperNode)) {
            throw new \Exception('You closed a templating tag which you never opened!');
        }
        if ($lastStackElement->getViewHelperClassName() !== $this->resolveViewHelperName($namespaceIdentifier, $methodIdentifier)) {
            throw new \Exception('Templating tags not properly nested. Expected: ' . $lastStackElement->getViewHelperClassName() . '; Actual: ' . $this->resolveViewHelperName($namespaceIdentifier, $methodIdentifier));
        }
    }

    /**
     * Handler for everything which is not a ViewHelperNode.
     *
     * This includes Text, array syntax, and object accessor syntax.
     *
     * @param \CarstenWalther\XliffGen\Parser\ParsingState $state
     * @param string                                       $text    Text to process
     * @param integer                                      $context one of the CONTEXT_* constants, defining whether we are inside or outside of ViewHelper arguments currently.
     *
     * @return void
     * @throws \Exception
     */
    protected function textAndShorthandSyntaxHandler(ParsingState $state, string $text, int $context) : void
    {
        $sections = preg_split($this->prepareTemplateRegularExpression(self::$SPLIT_PATTERN_SHORTHANDSYNTAX), $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        foreach ($sections as $section) {
            $matchedVariables = [];
            if (preg_match(self::$SCAN_PATTERN_SHORTHANDSYNTAX_OBJECTACCESSORS, $section, $matchedVariables) > 0) {
                $this->objectAccessorHandler($state, $matchedVariables['Object'], $matchedVariables['Delimiter'], ($matchedVariables['ViewHelper'] ?? ''), ($matchedVariables['AdditionalViewHelpers'] ?? ''));
            } elseif ($context === self::CONTEXT_INSIDE_VIEWHELPER_ARGUMENTS && preg_match(self::$SCAN_PATTERN_SHORTHANDSYNTAX_ARRAYS, $section, $matchedVariables) > 0) {
                // We only match arrays if we are INSIDE viewhelper arguments
                $this->arrayHandler($state, $matchedVariables['Array']);
            } else {
                $this->textHandler($state, $section);
            }
        }
    }

    /**
     * Handler for array syntax. This creates the array object recursively and
     * adds it to the current node.
     *
     * @param \CarstenWalther\XliffGen\Parser\ParsingState $state
     * @param string                                       $arrayText The array as string.
     *
     * @return void
     * @throws \Exception
     */
    protected function arrayHandler(ParsingState $state, string $arrayText) : void
    {
        /** @var $arrayNode ArrayNode */
        $node = new ArrayNode($this->recursiveArrayHandler($arrayText));
        $state->getNodeFromStack()->addChildNode($node);
    }

    /**
     * Handles the appearance of an object accessor (like {posts.author.email}).
     *
     * Handles ViewHelpers as well which are in the shorthand syntax.
     *
     * @param \CarstenWalther\XliffGen\Parser\ParsingState $state
     * @param string                                       $objectAccessorString String which identifies which objects to fetch
     * @param string                                       $delimiter
     * @param string                                       $viewHelperString
     * @param string                                       $additionalViewHelpersString
     *
     * @return void
     * @throws \Exception
     */
    protected function objectAccessorHandler(ParsingState $state, string $objectAccessorString, string $delimiter, string $viewHelperString, string $additionalViewHelpersString) : void
    {
        $viewHelperString .= $additionalViewHelpersString;
        $numberOfViewHelpers = 0;

        // The following post-processing handles a case when there is only a ViewHelper, and no Object Accessor.
        if ($delimiter === '' && $viewHelperString !== '') {
            $viewHelperString = $objectAccessorString . $viewHelperString;
            $objectAccessorString = '';
        }

        // ViewHelpers
        $matches = [];
        if ($viewHelperString !== '' && preg_match_all(self::$SPLIT_PATTERN_SHORTHANDSYNTAX_VIEWHELPER, $viewHelperString, $matches, PREG_SET_ORDER) > 0) {
            // The last ViewHelper has to be added first for correct chaining.
            foreach (array_reverse($matches) as $singleMatch) {
                if ($singleMatch['ViewHelperArguments'] !== '') {
                    $arguments = $this->postProcessArgumentsForObjectAccessor($this->recursiveArrayHandler($singleMatch['ViewHelperArguments']));
                } else {
                    $arguments = array();
                }
                $this->initializeViewHelperAndAddItToStack($state, $singleMatch['NamespaceIdentifier'], $singleMatch['MethodIdentifier'], $arguments);
                $numberOfViewHelpers++;
            }
        }

        // Object Accessor
        if ($objectAccessorString !== '') {
            /** @var $node ObjectAccessorNode */
            $node = new ObjectAccessorNode($objectAccessorString);
            $state->getNodeFromStack()->addChildNode($node);
        }

        // Close ViewHelper Tags if needed.
        for ($i = 0; $i < $numberOfViewHelpers; $i++) {
            $node = $state->popNodeFromStack();
        }
    }

    /**
     * Initialize the given ViewHelper and adds it to the current node and to
     * the stack.
     *
     * @param \CarstenWalther\XliffGen\Parser\ParsingState $state
     * @param string                                       $namespaceIdentifier Namespace identifier - being looked up in $this->namespaces
     * @param string                                       $methodIdentifier    Method identifier
     * @param array                                        $argumentsObjectTree Arguments object tree
     *
     * @return void
     * @throws \Exception
     */
    protected function initializeViewHelperAndAddItToStack(ParsingState $state, string $namespaceIdentifier, string $methodIdentifier, array $argumentsObjectTree) : void
    {
        if (!array_key_exists($namespaceIdentifier, $this->namespaces)) {
            throw new \Exception('Namespace could not be resolved. This exception should never be thrown!', 1224254792);
        }

        $resolvedViewHelperClassName = $this->resolveViewHelperName($namespaceIdentifier, $methodIdentifier);

        /** @var $currentViewHelperNode ViewHelperNode */
        $currentViewHelperNode = new ViewHelperNode($resolvedViewHelperClassName, $argumentsObjectTree);
        $state->getNodeFromStack()->addChildNode($currentViewHelperNode);
        $state->pushNodeToStack($currentViewHelperNode);
    }

    /**
     * Resolve a viewhelper name.
     *
     * @param string $namespaceIdentifier Namespace identifier for the view helper.
     * @param string $methodIdentifier    Method identifier, might be hierarchical like "link.url"
     *
     * @return string The fully qualified class name of the viewhelper
     */
    public function resolveViewHelperName(string $namespaceIdentifier, string $methodIdentifier) : string
    {
        $explodedViewHelperName = explode('.', $methodIdentifier);
        if (count($explodedViewHelperName) > 1) {
            $className = implode('\\', array_map('ucfirst', $explodedViewHelperName));
        } else {
            $className = ucfirst($explodedViewHelperName[0]);
        }
        $className .= 'ViewHelper';

        return $this->namespaces[$namespaceIdentifier] . '\\' . $className;
    }

    /**
     * Recursive function which takes the string representation of an array and
     * builds an object tree from it.
     *
     * Deals with the following value types:
     * - Numbers (Integers and Floats)
     * - Strings
     * - Variables
     * - sub-arrays
     *
     * @param string $arrayText Array text
     *
     * @return array the array node built up
     * @throws \Exception
     */
    protected function recursiveArrayHandler(string $arrayText) : array
    {
        $matches = [];
        if (preg_match_all(self::$SPLIT_PATTERN_SHORTHANDSYNTAX_ARRAY_PARTS, $arrayText, $matches, PREG_SET_ORDER) > 0) {
            $arrayToBuild = [];
            foreach ($matches as $singleMatch) {
                $arrayKey = $singleMatch['Key'];
                if (!empty($singleMatch['VariableIdentifier'])) {
                    $arrayToBuild[$arrayKey] = $singleMatch['VariableIdentifier'];
                } elseif (array_key_exists('Number', $singleMatch) && (!empty($singleMatch['Number']) || $singleMatch['Number'] === '0')) {
                    $arrayToBuild[$arrayKey] = (float)$singleMatch['Number'];
                } elseif ((array_key_exists('QuotedString', $singleMatch) && !empty($singleMatch['QuotedString']))) {
                    $argumentString = $this->unquoteString($singleMatch['QuotedString']);
                    $arrayToBuild[$arrayKey] = $this->buildArgumentObjectTree($argumentString);
                } elseif (array_key_exists('Subarray', $singleMatch) && !empty($singleMatch['Subarray'])) {
                    $arrayToBuild[$arrayKey] = $this->recursiveArrayHandler($singleMatch['Subarray']);
                } else {
                    throw new \Exception('This exception should never be thrown, as the array value has to be of some type (Value given: "' . var_export($singleMatch, true) . '"). Please post your template to the bugtracker at forge.typo3.org.');
                }
            }
            return $arrayToBuild;
        }
        throw new \Exception('This exception should never be thrown, there is most likely some error in the regular expressions. Please post your template to the bugtracker at forge.typo3.org.');
    }

    /**
     * Post process the arguments for the ViewHelpers in the object accessor
     * syntax. We need to convert an array into an array of (only) nodes
     *
     * @param array $arguments The arguments to be processed
     * @return array the processed array
     */
    protected function postProcessArgumentsForObjectAccessor(array $arguments) : array
    {
        foreach ($arguments as $argumentName => $argumentValue) {
            if (isset($argumentValue['data']['text'])) {
                $arguments[$argumentName] = (string)$argumentValue['data']['text'];
            }
        }
        return $arguments;
    }
}
