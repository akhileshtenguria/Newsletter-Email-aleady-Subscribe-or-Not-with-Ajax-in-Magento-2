<?php 
namespace Zestardtech\Newsletter\Controller\Index;

use Magento\Framework\App\Action\Action;

class Index extends Action
{
 protected $subscriberFactory;
 public function __construct(
     \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
 ) {
     $this->subscriberFactory= $subscriberFactory;
 }

 public function execute()
 {
		/*$websiteId = (int)$this->_storeManager->getStore()->getWebsiteId();
		
		$subscriber = $this->_subscriberFactory->create()->loadBySubscriberEmail($email, $websiteId);
		if ($subscriber->getId()
			&& (int)$subscriber->getSubscriberStatus() === Subscriber::STATUS_SUBSCRIBED) {
			throw new LocalizedException(
			    __('This email address is already subscribed.')
			);
		}*/

 }
}