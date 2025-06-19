<?php

class MetaTagIterator implements Iterator
{
    private DOMDocument $dom;
    private DOMXPath $xpath;
    private array $metaElements = [];
    private int $currentIndex = 0;

    public function __construct(string $htmlFile)
    {
        // Проверяем существование файла
        if (!file_exists($htmlFile)) {
            throw new Exception("Файл не найден: $htmlFile");
        }

    
        $this->dom = new DOMDocument('1.0', 'UTF-8');
        @$this->dom->loadHTMLFile($htmlFile, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        
        $this->xpath = new DOMXPath($this->dom);

        // Получаем все элементы head
        $headElements = $this->xpath->query('/html/head/*');

        
        foreach ($headElements as $element) {
            if ($element->nodeName === 'title' || 
                ($element->nodeName === 'meta' && 
                in_array($element->getAttribute('name'), ['description', 'keywords']))) {
                $this->metaElements[] = $element;
            }
        }
    }

    // Методы интерфейса Iterator
    public function current(): DOMElement
    {
        return $this->metaElements[$this->currentIndex];
    }

    public function next(): void
    {
        $this->currentIndex++;
    }

    public function key(): int
    {
        return $this->currentIndex;
    }

    public function valid(): bool
    {
        return isset($this->metaElements[$this->currentIndex]);
    }

    public function rewind(): void
    {
        $this->currentIndex = 0;
    }

    
    public function getAllMetaTags(): array
    {
        $result = [];
        foreach ($this->metaElements as $element) {
            if ($element->nodeName === 'title') {
                $result['title'] = $element->nodeValue;
            } elseif ($element->nodeName === 'meta') {
                $result[$element->getAttribute('name')] = $element->getAttribute('content');
            }
        }
        return $result;
    }
}


try {
    $iterator = new MetaTagIterator('index.html');
    
    
    foreach ($iterator as $metaElement) {
        if ($metaElement->nodeName === 'title') {
            echo 'Title: ' . $metaElement->nodeValue ; '<br>';
        } elseif ($metaElement->nodeName === 'meta') {
            echo 'Name: ' . $metaElement->getAttribute('name') ; '<br>';
            echo 'Content: ' . $metaElement->getAttribute('content') ; '<br>';
        }
        echo '---' . '<br>';
    }
    
    
    $metaTags = $iterator->getAllMetaTags();
    //print_r($metaTags);
} catch (Exception $e) {
    echo 'Ошибка: ' . $e->getMessage();
}
?>