<?php
/**
 *
 */

namespace Zestardtech\Newsletter\Plugin\Subscriber;

use Magento\Customer\Api\AccountManagementInterface as CustomerAccountManagement;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Newsletter\Model\SubscriptionManagerInterface;
use Magento\Framework\Validator\EmailAddress as EmailValidator;

/**
 * Class NewAction
 */
class Newaction extends \Magento\Newsletter\Controller\Subscriber\NewAction
{
    /**
     * @var CustomerAccountManagement
     */
    protected $customerAccountManagement;

    protected $resultJsonFactory;

     /**
     * @var SubscriptionManagerInterface
     */
    private $subscriptionManager;


    /**
     * @var EmailValidator
     */
    private $emailValidator;

    /**
     * Initialize dependencies.
     *
     * @param Context $context
     * @param SubscriberFactory $subscriberFactory
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param CustomerUrl $customerUrl
     * @param SubscriptionManagerInterface $subscriptionManager
     * @param CustomerAccountManagement $customerAccountManagement
     */
    public function __construct(
        Context $context,
        SubscriberFactory $subscriberFactory,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        CustomerUrl $customerUrl,
        CustomerAccountManagement $customerAccountManagement,
        SubscriptionManagerInterface $subscriptionManager,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
        // EmailValidator $emailValidator,
    )
    {
        $this->customerAccountManagement = $customerAccountManagement;
        $this->subscriptionManager = $subscriptionManager;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct(
            $context,
            $subscriberFactory,
            $customerSession,
            $storeManager,
            $customerUrl,
            $customerAccountManagement,
            $subscriptionManager,
            null
            // $EmailValidator

        );
    }

    /**
     * Retrieve available Order fields list
     *
     * @return array
     */
    public function aroundExecute($subject, $procede)
    {
        $response = [];
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
            $email = (string)$this->getRequest()->getPost('email');

            try {
                $this->validateEmailFormat($email);
                $this->validateGuestSubscription();
                $this->validateEmailAvailable($email);
                $websiteId = (int)$this->_storeManager->getStore()->getWebsiteId();

                $subscriber = $this->_subscriberFactory->create()->loadBySubscriberEmail($email, $websiteId);
                
               $status = $this->_subscriberFactory->create()->subscribe($email);
             /*  
                echo $subscriber->getSubscriberStatus();
                echo "--STATUS_SUBSCRIBED==". \Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED;
                echo "subscriber->getId".$subscriber->getId();*/
                if ($subscriber->getId()
                    && (int)$subscriber->getSubscriberStatus() ===  \Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED) {
                     $response = [
                        'status' => 'ERROR',
                        'msg' =>  __('This email address is already subscribed.'),
                    ];
                } else if ($status == \Magento\Newsletter\Model\Subscriber::STATUS_NOT_ACTIVE) {
                    $response = [
                        'status' => 'OK',
                        'msg' => 'The confirmation request has been sent.',
                    ];
                } else {
                    $response = [
                        'status' => 'OK',
                        'msg' => 'Thank you for your subscription.',
                    ];
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $response = [
                    'status' => 'ERROR',
                    'msg' => __('There was a problem with the subscription: %1', $e->getMessage()),
                ];
            } catch (\Exception $e) {
                $response = [
                    'status' => 'ERROR',
                    'msg' => __('Something went wrong with the subscription.'),
                ];
            }
        }

        return $this->resultJsonFactory->create()->setData($response);
    }
}