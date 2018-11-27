<?php

use \Codeception\Test\Unit;
use \app\stat\lang\Language;

class LanguageClassTest extends Unit
{
    /**
     * @var \FunctionalTester
     */
    protected $tester;
    /**
     *
     * @var Language
     */
    public $languageTestClass;
    
    protected function _before()
    {
        $this->languageTestClass = new class extends Language {
            public function getPathToFileForTest($moduleName) {
                return $this->getPathToFile($moduleName);
            }
        };
    }

    protected function _after()
    {
    }

    // tests
    public function testGetTranslationDefault()
    {
        $language = new Language();
        $language->isoCode = 'ru';
        $this->assertEquals($language->getTranslation('TITLE'), 'РОССПЕЦМАШ-СТАТ');
    }
    public function testGetTranslationPath() {
        $this->assertTrue(file_exists($this->languageTestClass->getPathToFileForTest($this->languageTestClass->defaultModuleName)));
    }
}