<?php

namespace CarstenWalther\XliffGen\Parser;

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
    const CONTEXT_INSIDE_VIEWHELPER_ARGUMENTS = 1;
    const CONTEXT_OUTSIDE_VIEWHELPER_ARGUMENTS = 2;

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
    protected $namespaces = array(
        'f' => 'TYPO3\Fluid\ViewHelpers'
    );

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
     * @return array
     * @throws \Exception
     */
    public function parse(string $templateString) : array
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
        $this->namespaces = array(
            'f' => 'TYPO3\Fluid\ViewHelpers'
        );
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
     * @return mixed
     * @throws \Exception
     */
    protected function buildObjectTree(array $splitTemplate, int $context) : array
    {
        $regularExpression_openingViewHelperTag = $this->prepareTemplateRegularExpression(self::$SCAN_PATTERN_TEMPLATE_VIEWHELPERTAG);
        $regularExpression_closingViewHelperTag = $this->prepareTemplateRegularExpression(self::$SCAN_PATTERN_TEMPLATE_CLOSINGVIEWHELPERTAG);

        $objectTree = [];
        foreach ($splitTemplate as $templateElement) {
            $matchedVariables = [];
            if (preg_match(self::$SCAN_PATTERN_CDATA, $templateElement, $matchedVariables) > 0) {
                // text
                $objectTree[] = [
                    'type' => 'text',
                    'data' => [
                        'text' => $matchedVariables[1]
                    ]
                ];
            } elseif (preg_match($regularExpression_openingViewHelperTag, $templateElement, $matchedVariables) > 0) {
                // opening Tag
                $tempObjectTree = [
                    'type' => 'openingTag',
                    'data' => [
                        'namespace' => $matchedVariables['NamespaceIdentifier'],
                        'method' => $matchedVariables['MethodIdentifier']
                    ]
                ];
                if ($matchedVariables['Attributes']) {
                    $tempObjectTree['data']['attributes'] = $this->parseArguments($matchedVariables['Attributes']);
                }
                $objectTree[] = $tempObjectTree;
            } elseif (preg_match($regularExpression_closingViewHelperTag, $templateElement, $matchedVariables) > 0) {
                // closing Tag
                $objectTree[] = [
                    'type' => 'closingTag',
                    'data' => [
                        'namespace' => $matchedVariables['NamespaceIdentifier'],
                        'method' => $matchedVariables['MethodIdentifier']
                    ]
                ];
            } else {
                // text and shorthand
                $sections = preg_split($this->prepareTemplateRegularExpression(self::$SPLIT_PATTERN_SHORTHANDSYNTAX), $templateElement, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
                foreach ($sections as $section) {
                    $matchedVariables = [];
                    if (preg_match(self::$SCAN_PATTERN_SHORTHANDSYNTAX_OBJECTACCESSORS, $section, $matchedVariables) > 0) {
                        // object
                        $objectTree[] = [
                            'type' => 'object',
                            'data' => [
                                'object' => $matchedVariables['Object'],
                                'delimiter' => $matchedVariables['Delimiter'],
                                'viewhelper' => $matchedVariables['ViewHelper'] ?? '',
                                'additionalViewhelper' => $matchedVariables['AdditionalViewHelpers'] ?? ''
                            ]
                        ];
                    } elseif ($context === self::CONTEXT_INSIDE_VIEWHELPER_ARGUMENTS && preg_match(self::$SCAN_PATTERN_SHORTHANDSYNTAX_ARRAYS, $section, $matchedVariables) > 0) {
                        // We only match arrays if we are INSIDE viewhelper arguments
                        $objectTree[] = [
                            'type' => 'text',
                            'data' => [
                                'text' => $matchedVariables['Array']
                            ]
                        ];
                    } else {
                        // text
                        $objectTree[] = [
                            'type' => 'text',
                            'data' => [
                                'text' => $section
                            ]
                        ];
                    }
                }
            }
        }
        return $objectTree;
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
                $tempObjectTree = $this->buildArgumentObjectTree($value);
                if (sizeof($tempObjectTree) === 1) {
                    $tempObjectTree = end($tempObjectTree);
                }
                $argumentsObjectTree[$argument] = $tempObjectTree;
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
     * @return array the corresponding argument object tree.
     * @throws \Exception
     */
    protected function buildArgumentObjectTree(string $argumentString) : array
    {
        if (strpos($argumentString, '{') === false && strpos($argumentString, '<') === false) {
            // text
            return [
                'type' => 'text',
                'data' => [
                    'text' => $argumentString
                ]
            ];
        }
        $splitArgument = $this->splitTemplateAtDynamicTags($argumentString);
        return $this->buildObjectTree($splitArgument, self::CONTEXT_INSIDE_VIEWHELPER_ARGUMENTS);
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
}
