import { useEffect, useState } from 'react'
import { getLoginAttemptsSettings, gt, onInputNumber, setLoginAttemptsSettings} from '../helpers';
import { GesimaticAccordion, LogedInAlerts, StatusIps, ToggleSwitch } from '../components';
import { sprintf } from '@wordpress/i18n'; // execute 'npm install @wordpress/i18n' to install library
import './GesimaticLoginAttemptsApp.css'

export const GesimaticLoginAttemptsApp = () => {

  // It gets the credentials for access to the API
  const { restUrl, nonce, availableRoles, isSuperAdmin } = gesimaticLoginAttemptsAdmin;

  // state to manage the login attempts settings
    const [settings, setSettings] = useState({
        attempts : 4, // max times of fail login before procede with a lock
        initialLock : 20, // initial period of time in minutes of a short lock
        multiplier: 2, //  Multiplier to increment the Periods of lock
        logedInAlert : true, //Sets the alert (sendig an email) at loged in user
        triggerRoles:['administrator'] //Roles that trigger the alert at loged in user
    });

    // State to manage the access attempts settings
    const [showAccessAttempts, setShowAccessAttempts] = useState(false);

    // State to manage the blocked ips Accordion
    const [showStatusIps, setShowStatusIps] = useState(false);

  // State to store the class and content alerts
  const [alert,setAlert] = useState({class:'gsmtc-display-none' ,content:''});


  useEffect(async () => {
        let data = await getLoginAttemptsSettings(restUrl, nonce);
        setSettings(data);
    
  },[])



  const onSubmit = async (event) =>{
        event.preventDefault();

        setAlert({class:'gsmtc-notice gsmtc-notice-info',content:gt('updating_information','Updating information.. Please wait')});

        const result = await setLoginAttemptsSettings(restUrl, nonce, settings)

        console.log ('onSubmit :', result);


        if ( result.success ){
            setAlert({class:'gsmtc-notice gsmtc-notice-success',content:gt('the_information_has_been','The information has been updated successfull')})
            setTimeout(() => {
                setAlert({class:'gsmtc-notice-fade-out',content:gt('the_information_has_been','The information has been updated successfull')});
                setTimeout(() => {setAlert({class:'gsmtc-display-none',content:''})},1000);
            },4000);
    
        } else setAlert({class:'gsmtc-notice gsmtc-notice-error',content:gt('the_information_has_not_been_updated','The information has not been updated')})

    }
    
    const onUpdateNetwork = async () => {

        setAlert({class:'gsmtc-notice gsmtc-notice-info',content:gt('updating_network','Updating network.. Please wait')});

        let newSettings = {...settings, updateNetwork : true}
        const result = await setSmtpSettings(restUrl, nonce, newSettings)

        if ( result ){
            setAlert({class:'gsmtc-notice gsmtc-notice-success',content:gt('the_network_has_been_updated_successfull','The network has been updated successfull')})
            setTimeout(() => {
                setAlert({class:'gsmtc-notice-fade-out',content:gt('the_information_has_been','The information has been updated successfull')});
                setTimeout(() => {setAlert({class:'gsmtc-display-none',content:''})},1000);
            },4000);
        } else setAlert({class:'gsmtc-notice gsmtc-notice-error',content:gt('the_network_has_not_been_updated','The network has not been updated')})
    }

    const onChangeTriggerAlerts = (newLogedAlerts, newTriggerRoles) => {
        setSettings({...settings,logedInAlert : newLogedAlerts, triggerRoles: newTriggerRoles})
    }
    return(
        <div className="wrap">
            <h2>{ gt('access_attempts','Access attempts') }</h2>
            <GesimaticAccordion
                showHide={showAccessAttempts}
                title={gt('settings','Settings')}
                openLabel={gt('hide_settings','Hide settings')}
                closedLabel={gt('show_settings','Show settings')}
                onChange={setShowAccessAttempts}
            >
            <form onSubmit={onSubmit}>
                <table className="form-table">
                    <tbody>
                        <tr>
                            <th scope="row"><label for="gsmtc-accessAttempts">{gt('acess_attempts_allowed','Access attempts allowed')}</label></th>
                            <td>
                                <input type="number" id="gsmtc-accessAttempts" name="attempts" value={settings.attempts} min="1" max="10" step="1" onInput={(event) => setSettings(onInputNumber(event,settings))} />
                                <p className='description'>{gt('attempts_allowed_defore_lockout','Attempts allowed before lockout')}</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="gsmtc-shortLock">{gt('initial_lockout_period','Initial lockout period')}</label></th>
                            <td>
                                <input type="number" id="gsmtc-shortLock" name="initialLock" value={settings.initialLock} min="5" max="60" step="5" onInput={(event) => setSettings(onInputNumber(event,settings))}/>
                                <p className='description' >{gt('minutes_of_initial_lockout_period','Minutes of initial lockout period')}</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="gsmtc-periodAttempts">{gt('multiplier_of_the_period_of_block','Multiplier of the period of block')}</label></th>
                            <td>
                                <input type="number" id="gsmtc-periodAttempts" name="multiplier" value={settings.multiplier} min="1" max="10" step="1" onInput={(event) => setSettings(onInputNumber(event,settings))}/>
                                <p className='description' >{gt('multiplier_to_increase_the_blocking_period_in','Multiplier to increase the blocking period in successive blocks, without resetting.')}</p>
                            </td>
                        </tr>                    
                    </tbody> 
                </table>
                        <p style={{fontWeight: 'bold'}}>
                            {sprintf(
                                    gt('after_failed_access_attempts','After %1$d failed access attempts from the same IP, access will be blocked for an initial period of %2$d minutes. If after this period another %1$d failed access attempts occur, the previous blocking period will be multiplied by %3$d and access will remain blocked during that period. This process continues until a successful login is achieved, at which point the failed access attempts for the IP are reset.'),
                                    settings.attempts,
                                    settings.initialLock,
                                    settings.multiplier
                                )
                            }
                        </p>                    
                <LogedInAlerts
                    logedInAlert={settings.logedInAlert}
                    triggerRoles={settings.triggerRoles}
                    availableRoles={availableRoles}
                    onChange={onChangeTriggerAlerts}
                />
                <div className={alert.class}>
                    <p>{alert.content}</p>
                </div>            
                <p className='submit'>
                    <input type="submit" name="submit-attempts" id="submit-attempts" className="button button-primary" value={ gt('update_ettings','Update settings') } style={{marginRight: "25px"}}/>
                    { (isSuperAdmin ) && <input type="button" name="submit-attempts-network" id="submit-attempts-network" className="button button-primary" value={ gt('update_all_network','Update all network') }  onClick={onUpdateAttemptsNetwork} /> }
                </p>
            </form>
        </GesimaticAccordion>
        <div style={{display: (settings.enabled)? 'block' : 'none'}}>
        <GesimaticAccordion
            showHide={showStatusIps}
            title={gt('status_ips','Status ips')}
            openLabel={gt('hide_status_ips','Hide status ips')}
            closedLabel={gt('show_status_ips','Show status ips')}
            onChange={setShowStatusIps}
        >
            <StatusIps
                restUrl={restUrl}
                nonce={nonce}
                isShown={showStatusIps}
            />
        </GesimaticAccordion>
        </div>
            <div className="gsmtc-notice gsmtc-notice-info">
                <p>{gt('this_plugin_temporarily_stores_the_IP','This plugin temporarily stores the IP addresses of failed login attempts to prevent brute-force attacks. IPs are automatically deleted after a successful login. We recommend including this information in your Privacy Policy.')}</p>
            </div>                
    </div>
    
    )
}