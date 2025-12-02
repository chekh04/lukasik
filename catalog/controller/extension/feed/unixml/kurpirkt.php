<?php
class ControllerExtensionFeedUnixmlKurpirkt extends Controller {
  public function index() {

    $feed = 'kurpirkt';
    $controller = str_replace($feed, 'startup', $this->request->get['route']);
    $startup = $this->load->controller($controller, array('feed'=>$feed));
    $xml = false;

    //XML_body
    //headerXML
    $xml .= '<?xml version="1.0" encoding="UTF-8" ?>';
    $xml .= '<root>';

    $xml = $this->unixml->exportToXml($startup, $xml, "start");
    //headerXML

    //generateXML
      for($startup['iteration'] = 0; 1; $startup['iteration']++){

        $controller_data = $this->load->controller($controller, $startup);
        $startup['stat'] = $controller_data['data']['stat'];

        if($controller_data['products']){

          foreach($controller_data['products'] as $product_id => $product){
            $xml .= '<item>';
            $xml .= '<name>' . $product['name'] .  '</name>';
            $xml .= '<link>' . $product['url'] .  '</link>';
            $xml .= '<price>' . ($product['special']?$product['special']:$product['price']) .  '</price>';
            $xml .= '<image>' . $product['image'] .  '</image>';
            if($product['images']){
              foreach($product['images'] as $image){
                $xml .= '<image>' . $image .  '</image>';
              }
            }
            $xml .= '<manufacturer>' . $product['manufacturer'] .  '</manufacturer >';
            $xml .= '<category>' . $product['category'] .  '</category >';
            $xml .= '<category_link>' . $this->url->link('product/category', 'path=' . $product['category_id']) .  '</category_link>';
            if(isset($product['category_full'])){
              $xml .= '<category_full>' . $product['category_full'] .  '</category_full>';
            } //
            $xml .= '<in_stock>ir</in_stock>';
            //$xml .= '<in_stock>' . $product['quantity'] .  '</in_stock>';
            foreach($product['attributes_full'] as $attribute){
              //$xml .= '<' . $attribute['name'] . '>' . $attribute['text'] .  '</' . $attribute['end'] . '>';
            }
            $xml .= '</item>';
          }
        } else {
          break;
        }

        $xml = $this->unixml->exportToXml($controller_data['data'], $xml);
      }
    //generateXML

    //footerXML
    $xml .= '</root>';

    /*
    4.2
    $data['product_type'] = true;

    4.3
    $catfull = array();
    foreach(explode('_', $product['category_path']) as $cat_id){
    if($data['categories_xml'][$cat_id]){
    $catfull[] = $data['categories_xml'][$cat_id]['name'];
    }
    }
    if($catfull){
    $product['category_full'] = implode(' &gt;&gt; ', $catfull);
    }
    */

    $this->unixml->exportToXml($controller_data['data'], $xml, "finish");
    //footerXML

    //XML_body

  }
}
