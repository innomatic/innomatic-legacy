<?php
/**
 * Innomatic
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2014 Innoteam Srl
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
*/
namespace Innomatic\Application;

/*!
 @class ApplicationComponentRegister
 @abstract Application components register handling.
 */
class ApplicationComponentRegister
{
    /*! @public rootda DataAccess class - Innomatic database handler. */
    public $rootda;

    /*!
     @abstract Class constructor.
     @param rootda DataAccess class - Innomatic database handler.
     */
    public function __construct(\Innomatic\Dataaccess\DataAccess $rootda)
    {
        $this->rootda = $rootda;
    }

    //
    // Application components registration routines
    //

    /*!
     @abstract Registers a application component in the components register.
     @param appname string - name if the application.
     @param category string - application component category complete name
(not id number).
     @param componentname string - component name.
     @param ignoreduplicate boolean - if you want to ignore a previous
    registration with same parameters.
     @result True if registered.
     */
    public function registerComponent(
        $appname,
        $category,
        $componentname,
        $domainid = '',
        $override = ApplicationComponent::OVERRIDE_NONE,
        $ignoreduplicate = false
    )
    {
        if (
            (
                $this->checkRegisterComponent(
                    $category,
                    $componentname,
                    $domainid,
                    '',
                    $override
                ) == false
            ) or $ignoreduplicate == true
        ) {
            $tmpquery = $this->rootda->execute(
                'SELECT id FROM applications_components_types WHERE typename='
                . $this->rootda->formatText($category)
            );
            $tmpdata = $tmpquery->getFields();
            $this->rootda->execute(
                'INSERT INTO applications_components_register VALUES ( ' .
                $this->rootda->formatText($appname) . ',' .
                $this->rootda->formatText($tmpdata['id']) . ',' .
                $this->rootda->formatText($componentname) . ',' .
                $this->rootda->formatText($domainid) . ',' .
                $this->rootda->formatText($override) .
                ')'
            );
            return true;
        }
        return false;
    }

    /*!
     @abstract Checks if a certain component has been already registered.
     @param category string - application component category complete name
    (not id number).
     @param componentname string - component name.
     @param appname string - name of the application.
     @param exclude boolean - if you want to exclude appname (if given)
    from check.
     @result Application name if registered.
     */
    public function checkRegisterComponent(
        $category,
        $componentname,
        $domainid = '',
        $appname = '',
        $override = ApplicationComponent::OVERRIDE_NONE,
        $exclude = false
    )
    {
        $result = false;

        $catquery = $this->rootda->execute(
            'SELECT id FROM applications_components_types WHERE typename='
            . $this->rootda->formatText($category)
        );
        $catdata = $catquery->getFields();

        $querystr = 'categoryid=' . $this->rootda->formatText($catdata['id']) .
            ' AND componentname=' . $this->rootda->formatText($componentname) .
            ' AND domainid=' . $this->rootda->formatText($domainid) .
            ' AND override=' . $this->rootda->formatText($override);

        if (!empty($appname)) {
            $querystr .= ' AND appname ' . ($exclude ? '!=' : '=')
                . ' ' . $this->rootda->formatText($appname);
        }

        $tmpquery = $this->rootda->execute(
            'SELECT * FROM applications_components_register WHERE ' . $querystr
        );
        if ($tmpquery->getNumberRows()) {
            $result = &$tmpquery->getFields();
        }

        return $result;
    }

    /*!
     @abstract Unregisters an component.
     @param appname string - name if the application.
     @param category string - application component category complete name
    (not id number).
     @param componentname string - component name.
     @result True if unregistered.
     */
    public function unregisterComponent(
        $appname,
        $category,
        $componentname,
        $domainid = '',
        $override = ApplicationComponent::OVERRIDE_NONE
    )
    {
        $regdata = $this->checkRegisterComponent(
            $category,
            $componentname,
            $domainid,
            $appname,
            $override
        );
        if ($regdata != false) {
            $catquery = $this->rootda->execute(
                'SELECT id FROM applications_components_types WHERE typename='
                . $this->rootda->formatText($category)
            );
            $catdata = $catquery->getFields();

            $this->rootda->execute(
                'DELETE FROM applications_components_register WHERE appname='
                . $this->rootda->formatText($appname) .
                ' AND categoryid='.$this->rootda->formatText($catdata['id']) .
                ' AND componentname='.$this->rootda->formatText(
                    $componentname
                ) .
                ' AND domainid='.$this->rootda->formatText($domainid) .
                ' AND override='.$this->rootda->formatText($override)
            );
            return true;
        }
        return false;
    }
}
