<?php

namespace app\stat;

use app\stat\exceptions\DefaultException;
/**
 * Класс для организации постраничной навигации
 *
 * @author kotov
 */
class Pagination {
    
    /** @var int число страниц */
    protected $pagesCount;
    /** @var int текущая страница */
    protected $pageNum;
    /** @var int Максимальное число видимых страниц на экране  */
    protected $maxPagesDisplay;


    public function __construct(int $pagesCount = 0,int $currentPage = 1,int $maxPagesDisplay = 10) {
        $this->pagesCount = $pagesCount;
        $this->pageNum = $currentPage;
        $this->maxPagesDisplay = $maxPagesDisplay;
    }
    
    public function setParams(array $params) {
        foreach ($params as $param => $value) {
            if (property_exists($this, $param)) {
                $this->$param = $value;
            }
        }
    }
    /**
     * Вернуть массив для постраничной навигации
     * @return array
     */
    public function getPagination() {
        if (empty($this->pagesCount) && $this->pagesCount !== 0) {
           throw new DefaultException('NotConfiguredObject','Объект постраничной навигации сконфигурирован не правильно');
        }
        $aResult = array();
        $halfPagesLeft = ceil($this->maxPagesDisplay / 2);
        $halfPagesRight = $this->maxPagesDisplay - $halfPagesLeft;
        
        if ($this->pagesCount < $this->maxPagesDisplay) {
            $startPage = 1;
            $endPage = $this->pagesCount;
            $startIndex = 0;
            $rightFlag = false;
        } else {
            if ($this->pageNum > $halfPagesLeft) {
                $startPage = $this->pageNum - $halfPagesLeft + 1;
                $aResult[0] = array ('page' => '1', 'val' => '«');
                $aResult[1] = array ('page' => $this->pageNum-1, 'val' => '<');
                $startIndex = 2;
                $endPage = $this->pageNum + $halfPagesRight - 1;
                if ($this->pageNum > ($this->pagesCount - $halfPagesRight)) {
                    $rightFlag = false;
                    $startPage = $this->pagesCount - $this->maxPagesDisplay + 1;
                    $endPage = $this->pagesCount;
                } else {
                    $startPage = $this->pageNum - $halfPagesLeft + 1;
                    $endPage = $this->pageNum + $halfPagesRight - 1;
                    $rightFlag = true;
                }
            } else {
                $startIndex = 0;
                $rightFlag = true;
                $startPage = 1;
                $endPage = $this->maxPagesDisplay - 1;
            }            
        }
        for ($i=$startPage;$i <=$endPage; $i++)
        {
            $aResult[$startIndex]= array ( 'page' => $i, 'val' => $i);
            if ($this->pageNum == $i) {
                $aResult[$startIndex]['active'] = 1;
            }
            $startIndex++;
        }
        if ($rightFlag) {
            $aResult[$startIndex++] = array ('page' => ($this->pageNum+1), 'val' => '>');
            $aResult[$startIndex++] = array ('page' => ($this->pagesCount), 'val' => '»');
        }   
        return $aResult;
    }
}
