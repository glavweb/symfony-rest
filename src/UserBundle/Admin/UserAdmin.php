<?php

namespace UserBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use FOS\UserBundle\Model\UserManagerInterface;

/**
 * Class UserAdmin
 * @package UserBundle\Admin
 */
class UserAdmin extends Admin
{
    /**
     * The base route pattern used to generate the routing information
     *
     * @var string
     */
    protected $baseRoutePattern = 'user';

    /**
     * The base route name used to generate the routing information
     *
     * @var string
     */
    protected $baseRouteName = 'user';

    /**
     * @var UserManagerInterface
     */
    protected $userManager;

    /**
     * {@inheritdoc}
     */
    public function getFormBuilder()
    {
        $this->formOptions['data_class'] = $this->getClass();

        $options = $this->formOptions;
        $options['validation_groups'] = (!$this->getSubject() || is_null($this->getSubject()->getId())) ? 'Registration' : 'Profile';

        $formBuilder = $this->getFormContractor()->getFormBuilder($this->getUniqid(), $options);
        $this->defineFormBuilder($formBuilder);

        return $formBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getExportFields()
    {
        // avoid security field to be exported
        return array_filter(parent::getExportFields(), function ($v) {
            return !in_array($v, array('password', 'salt'));
        });
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('username')
            ->add('email')
            ->add('enabled', null, array('editable' => true))
            ->add('locked', null, array('editable' => true))
            ->add('createdAt')
        ;

        if ($this->isGranted('ROLE_ALLOWED_TO_SWITCH')) {
            $listMapper
                ->add('impersonating', 'string', array('template' => 'SonataUserBundle:Admin:Field/impersonating.html.twig'))
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $filterMapper)
    {
        $filterMapper
            ->add('id')
            ->add('username')
            ->add('email')
            ->add('locked')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $authorizationChecker = $this->getConfigurationPool()->getContainer()->get('security.authorization_checker');

        $showMapper
            ->with('General')
                ->add('username')
                ->add('email')
            ->end()
            ->with('Profile')
                ->add('firstname')
                ->add('lastname')
                ->add('dateOfBirth')
                ->add('gender')
                ->add('locale')
                ->add('timezone')
                ->add('phone')
            ->end()
        ;

        if ($authorizationChecker->isGranted('ROLE_ADMIN')) {
            $showMapper
                ->with('Groups')
                    ->add('groups')
                ->end()
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $authorizationChecker = $this->getConfigurationPool()->getContainer()->get('security.authorization_checker');
        $formMapper
            ->tab('User')
                ->with('Profile', array('class' => 'col-md-6'))->end()
                ->with('General', array('class' => 'col-md-6'))->end()
            ->end()
        ;

        if ($authorizationChecker->isGranted('ROLE_ADMIN')) {
            $formMapper
                ->tab('Security')
                    ->with('Status', array('class' => 'col-md-6'))->end()
                    ->with('Groups', array('class' => 'col-md-6'))->end()
                ->end()
                ->tab('Additional roles')
                    ->with('Additional roles', array('class' => 'col-md-12'))->end()
                ->end()
            ;
        }

        $now = new \DateTime();

        $formMapper
            ->tab('User')
                ->with('General')
                    ->add('username')
                    ->add('email')
                    ->add('plainPassword', 'text', array(
                        'required' => (!$this->getSubject() || is_null($this->getSubject()->getId())),
                    ))
                ->end()
                ->with('Profile')
                    ->add('firstname', null, array('required' => false))
                    ->add('lastname', null, array('required' => false))
                    ->add('phone', null, array('required' => false))
                    ->add('dateOfBirth', 'sonata_type_date_picker', array(
                        'years'       => range(1900, $now->format('Y')),
                        'dp_min_date' => '1-1-1900',
                        'dp_max_date' => $now->format('c'),
                        'required'    => false,
                    ))
                    ->add('gender', 'sonata_user_gender', array(
                        'required'           => true,
                        'translation_domain' => $this->getTranslationDomain(),
                    ))
                    ->add('locale', 'locale', array('required' => false))
                    ->add('timezone', 'timezone', array('required' => false))
                ->end()
            ->end()
        ;

        if ($authorizationChecker->isGranted('ROLE_ADMIN') && $this->getSubject() && !$this->getSubject()->hasRole('ROLE_SUPER_ADMIN')) {
            $formMapper
                ->tab('Security')
                    ->with('Status')
                        ->add('locked', null, array('required' => false))
                        ->add('expired', null, array('required' => false))
                        ->add('enabled', null, array('required' => false))
                        ->add('credentialsExpired', null, array('required' => false))
                    ->end()
                    ->with('Groups')
                        ->add('groups', 'sonata_type_model', array(
                            'required' => false,
                            'expanded' => true,
                            'multiple' => true,
                            'class'    => 'UserBundle:Group'
                        ))
                        ->end()
                ->end()
                ->tab('Additional roles')
                    ->with('Additional roles')
                        ->add('realRoles', 'sonata_security_roles', array(
                            'label'    => false,
                            'expanded' => true,
                            'multiple' => true,
                            'required' => false,
                        ))
                    ->end()
                ->end()
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate($user)
    {
        $this->getUserManager()->updateCanonicalFields($user);
        $this->getUserManager()->updatePassword($user);
    }

    /**
     * @param UserManagerInterface $userManager
     */
    public function setUserManager(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * @return UserManagerInterface
     */
    public function getUserManager()
    {
        return $this->userManager;
    }
}
