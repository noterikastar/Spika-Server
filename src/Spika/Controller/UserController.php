<?php
/**
 * Created by IntelliJ IDEA.
 * User: dinko
 * Date: 10/22/13
 * Time: 2:45 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Spika\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;

class UserController extends SpikaBaseController
{


    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];
        $self = $this;

        $this->setupUpdateUserMethod($self,$app,$controllers);
        $this->setupFindUserMethod($self,$app,$controllers);
        $this->setupActivitySummaryMethod($self,$app,$controllers);
        $this->setupMessagesMethod($self,$app,$controllers);
        $this->setupGetAvatarFileIdMethod($self,$app,$controllers);
        $this->setupContactsMethod($self,$app,$controllers);

        return $controllers;
    }

    private function setupUpdateUserMethod($self,$app,$controllers){
        $controllers->post('/updateUser',
            function (Request $request) use ($app,$self) {
                
                $currentUser = $app['currentUser'];
                $userData = $request->getContent();

                if(!$self->validateRequestParams($userData,array(
                    'name',
                    'email',
                    'online_status',
                    'max_contact_count',
                    'max_favorite_count'
                ))){
                    return $self->returnErrorResponse("insufficient params");
                }

                $userDataArray=json_decode($userData,true);

                $result = $app['spikadb']->updateUser($currentUser['_id'],$userDataArray);
                $app['monolog']->addDebug("Update API called with user id: \n {$userData} \n");
                
                return json_encode($result);
            }
            
        )->before($app['beforeTokenChecker']);
    }



        /*
         * example calls
         *
         * find by id
         * curl -vX GET http://192.168.1.101:8080/wwwroot/api/FindUser/id/13583389e04adfaa3bac7ae52501a92a -H "token: pFfQJob0Q9kKLAxKEeNeKiLxLb0DPWcfCs6lRhlH" -H "user_id: 13583389e04adfaa3bac7ae52501809e"
         *
         * find by email
         * curl -vX GET http://192.168.1.101:8080/wwwroot/api/FindUser/email/dinko.klobucar@clover-studio.com -H "token:  "user_id: 13583389e04adfaa3bac7ae52501809e"
         *
         */

    private function setupFindUserMethod($self,$app,$controllers){
        $controllers->get('/findUser/{type}/{value}',
            function ($type,$value) use ($app,$self) {

                if(empty($value) || empty($type)){
                    return $self->returnErrorResponse("insufficient params");
                }

                switch ($type){
                    case "id":
                        $result = $app['spikadb']->findUserById($value);
                        $app['monolog']->addDebug("FindUserById API called with user id: \n {$value} \n");
                        break;
                    case "email":
                        $result = $app['spikadb']->findUserByEmail($value);
                        $app['monolog']->addDebug("FindUserByEmail API called with email: \n {$value} \n");
                        break;
                    case "name":
                        $result = $app['spikadb']->findUserByName($value);
                        $app['monolog']->addDebug("FindUserByName API called with name: \n {$value} \n");
                        break;
                    default:
                        return $self->returnErrorResponse("unknown search key");

                }

                if($result == null)
                    return $self->returnErrorResponse("No user found");
                    
                return json_encode($result);
                
            }
        )->before($app['beforeTokenChecker']);
    }

    /*
     * curl -vX GET http://192.168.1.101:8080/wwwroot/api/ActivitySummary -H "token: pFfQJob0Q9kKLAxKEeNeKiLxLb0DPWcfCs6lRhlH" -H "user_id: 13583389e04adfaa3bac7ae52501809e"
     */
    private function setupActivitySummaryMethod($self,$app,$controllers){

        $controllers->get('/activitySummary',
            function () use ($app,$self) {
				
				$user = $app['currentUser'];
				$userId = $user['_id'];
				
                if(empty($userId)){
                    return $self->returnErrorResponse("insufficient params");
                }

                $result = $app['spikadb']->getActivitySummary($userId);
                $app['monolog']->addDebug("ActivitySummary API called with user id: \n {$userId} \n");
				

                return json_encode($result);
            }
        )->before($app['beforeTokenChecker']);
    }

    private function setupMessagesMethod($self,$app,$controllers){
        $controllers->get('/Messages',
            function (Request $request) use ($app,$self) {

                /*
                $params=array();
                $params['startkey']=$request->get('startkey');
                $params['endkey']=$request->get('endkey');
                $params['descending']=$request->get('descending');
                $params['limit']=$request->get('limit');
                $params['skip']=$request->get('skip');
                */

                return print_r($request,true);

                if(!$self->validateRequestParams($requestBody,array(
                    'startkey',
                    'endkey',
                    'descending',
                    'limit',
                    'skip'
                ))){
                    return $self->returnErrorResponse("insufficient params");
                }



                  /*
                $result = $app['spikadb']->getUserMessages($start);
                $app['monolog']->addDebug("MessagesFrom API called with start key: \n {$start} \n");


                return json_encode($result);*/
            }
        )->before($app['beforeTokenChecker']);
    }

    private function setupGetAvatarFileIdMethod($self,$app,$controllers){

        $controllers->get('/GetAvatarFileId/{user_id}',
            function ($user_id) use ($app,$self) {

                if(empty($user_id)){
                    return $self->returnErrorResponse("insufficient params");
                }

                $result = $app['spikadb']->getAvatarFileId($user_id);
                $app['monolog']->addDebug("GetAvatarFileId API called with user id: \n {$user_id} \n");


                return json_encode($result);
            }
        );
    }

    private function setupContactsMethod($self,$app,$controllers){

        $controllers->get('/Contacts/{user_id}/{include_docs}',
            function ($user_id, $include_docs) use ($app,$self) {

                $result = $app['spikadb']->getUserContacts($user_id, $include_docs);
                $app['monolog']->addDebug("Contacts API called with user id: \n {$user_id} \n");

                return json_encode($result);
            }
        )->before($app['beforeTokenChecker']);
    }


}

















