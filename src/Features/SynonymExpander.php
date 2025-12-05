<?php

namespace PlusInfoLab\Search\Features;

class SynonymExpander
{
    /**
     * Synonym configuration.
     */
    protected array $config;

    /**
     * Synonym dictionary.
     */
    protected array $dictionary = [];

    /**
     * Create a new synonym expander instance.
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->loadDictionary();
    }

    /**
     * Load synonym dictionary.
     */
    protected function loadDictionary(): void
    {
        // Load from config
        $this->dictionary = $this->config['dictionary'] ?? [];

        // Load from file if specified
        if (isset($this->config['file']) && file_exists($this->config['file'])) {
            $fileContent = file_get_contents($this->config['file']);
            $fileDictionary = json_decode($fileContent, true) ?? [];
            $this->dictionary = array_merge($this->dictionary, $fileDictionary);
        }
    }

    /**
     * Expand query with synonyms.
     */
    public function expand(string $query): string
    {
        if (! ($this->config['enabled'] ?? false) || empty($this->dictionary)) {
            return $query;
        }

        $words = explode(' ', $query);
        $expandedWords = [];

        foreach ($words as $word) {
            $normalizedWord = mb_strtolower(trim($word));
            $expandedWords[] = $word;

            // Check if word has synonyms
            if (isset($this->dictionary[$normalizedWord])) {
                $synonyms = $this->dictionary[$normalizedWord];

                // Add synonyms as OR alternatives
                foreach ($synonyms as $synonym) {
                    $expandedWords[] = 'OR ' . $synonym;
                }
            }
        }

        return implode(' ', $expandedWords);
    }

    /**
     * Get synonyms for a word.
     */
    public function getSynonyms(string $word): array
    {
        $normalizedWord = mb_strtolower(trim($word));

        return $this->dictionary[$normalizedWord] ?? [];
    }

    /**
     * Add synonym to dictionary.
     */
    public function addSynonym(string $word, array $synonyms): void
    {
        $normalizedWord = mb_strtolower(trim($word));
        $this->dictionary[$normalizedWord] = array_merge(
            $this->dictionary[$normalizedWord] ?? [],
            $synonyms
        );
    }

    /**
     * Get the full dictionary.
     */
    public function getDictionary(): array
    {
        return $this->dictionary;
    }
}
