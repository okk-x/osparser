<?php

namespace osparser;

interface StoreParser{
    const PRODUCT_ALL=15;
    const PRODUCT_TITLE=1;
    const PRODUCT_PRICE=2;
    const PRODUCT_DESC=4;
    const PRODUCT_IMG=8;

    public function category($url);
    public function page($page);
    public function product($url);
    public function countPages();
    public function parse();
    public function setInterval($min,$max);
    public function productLinksFromPage();
    public function setting($setting);
}
