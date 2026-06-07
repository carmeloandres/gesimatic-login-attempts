import { useEffect, useState } from 'react'
import { gt } from '../helpers';
import { GesimaticAccordion } from '../components';
//import { GsmtcAccordion, TestEmail, ToggleSwitch, WpPasswordInput } from './components';
//import { getSmtpSettings, gt, setSmtpSettings } from './helpers'
import './GesimaticLoginAttemptsApp.css'

export const GesimaticLoginAttemptsApp = () => {

  // It gets the credentials for access to the API
  const { restUrl, nonce, isSuperAdmin } = gesimaticLoginAttemptsAdmin;

  // state to manage the login attempts settings
    const [settings, setSettings] = useState({
        enabled : false, // Enables or Disbles the Access Attempts protection
        attempts : 4, // max times of fail login before procede with a lock
        initialLock : 20, // initial period of time in minutes of a short lock
        multiplier: 2, //  Multiplier to increment the Periods of lock
        logedInAlert : true, //Sets the alert (sendig an email) at loged in user
        blocksEnumerationAccess: true, // Set to true, blocks the enumeration accesses to  ?author or /author/username
        hideUsersEndpoints: true, // Set to true hide the access to users endpoint throught the REST API
        disablePostAuthorInfo: true, // Set to true disables the author info in posts 
        triggerRoles:['administrator'] //Roles that trigger the alert at loged in user
    });

    // State to manage the access attempts settings
    const [showAccessAttempts, setShowAccessAttempts] = useState(false);

  // State to store the bloqued ip status
  //const [bloqued,setBloqued] = useState(false);

  // State to store the class and content alerts
  const [alert,setAlert] = useState({class:'gsmtc-display-none' ,content:''});

  // State to manage the smtp settings
  const [showSmtpSettings, setShowSmtpSettings] = useState(true);

  // State to manage the show test email acordion
  const [showTestEmail, setShowTestEmail] = useState(false);

  // State to manage the port value
  const [selectPortValue, setSelectPortValue] = useState(587);

  useEffect(async () => {
//    let data = await getSmtpSettings(restUrl, nonce);
  //  setSettings(data);
//    console.log ('gesimaticSmtpAdmin :', gesimaticSmtpAdmin);
  },[])

  useEffect(() => {
//    if (settings.port != 587 && settings.port != 465 && settings.port != 25 ){
  //      setSelectPortValue('custom')
    //}
    //else setSelectPortValue(settings.port)
 //   console.log ('settings :', settings);
  },[settings])

  const onChangeSelectPort = (event) => {
    let value = event.target.value;
    if (value !== "587" && value !== "465" && value !== "25" ){
        setSelectPortValue('custom')
    }
    else {
        setSettings({...settings, port : parseInt(value)})
        setSelectPortValue(parseInt(value))
    }
  }



  const onSubmit = async (event) =>{
        event.preventDefault();

        setAlert({class:'gsmtc-notice gsmtc-notice-info',content:gt('updating_information','Updating information.. Please wait')});

        const result = await setSmtpSettings(restUrl, nonce, settings)

//        console.log ('onSubmit :', result);


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
                            <th scope="row"><label >{gt('enable_disables_login_attempts_functionality','Enable / Disables login attempts functionality')}</label></th>
                            <td>
                                <ToggleSwitch
                                    value={settings.enabled}
                                    onChange={(newValue) => setSettings({...settings,enabled : newValue})}
                                />
                                <p className='description'>{(settings.enabled == true ) ? __('Enabled',gsmtc) : __('Disabled',gsmtc)}</p>
                            </td>
                        </tr>
                        { settings.enabled &&
                        <> 
                        <tr>
                            <th scope="row"><label for="gsmtc-accessAttempts">{__('Access attempts allowed',gsmtc)}</label></th>
                            <td>
                                <input type="number" id="gsmtc-accessAttempts" name="attempts" value={settings.attempts} min="1" max="10" step="1" onInput={(event) => setSettings(onInputNumber(event,settings))} />
                                <p className='description'>{__('Attempts allowed before lockout',gsmtc)}</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="gsmtc-shortLock">{__('Initial lockout period',gsmtc)}</label></th>
                            <td>
                                <input type="number" id="gsmtc-shortLock" name="initialLock" value={settings.initialLock} min="5" max="60" step="5" onInput={(event) => setSettings(onInputNumber(event,settings))}/>
                                <p className='description' >{__('Minutes of initial lockout period',gsmtc)}</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="gsmtc-periodAttempts">{__('Multiplier of the period of block',gsmtc)}</label></th>
                            <td>
                                <input type="number" id="gsmtc-periodAttempts" name="multiplier" value={settings.multiplier} min="1" max="10" step="1" onInput={(event) => setSettings(onInputNumber(event,settings))}/>
                                <p className='description' >{__('Multiplier to increase the blocking period in successive blocks, without resetting.',gsmtc)}</p>
                            </td>
                        </tr>
                        </>
                        }
                    </tbody> 
                </table>
                { settings.enabled &&                 
                    <p style={{fontWeight: 'bold'}}>{__('After',gsmtc)+' '+settings.attempts+' '
                        +__('failed access attempts, from the same ip, access will be bloqued for the initial period of',gsmtc)+' '+settings.initialLock+' '
                        +__('minutes. If after this period another',gsmtc)+' '+settings.attempts+' '+__('failed access attempts occur, the previous blocking period will be increased multiplied by',gsmtc)
                        +' '+settings.multiplier+' '+__('and blocking will proceed during that period. And so on until a successfull login is achived, at which point the access failures from the ip are reset.',gsmtc)} 
                    </p>
                }
                <table className="form-table">
                    <tbody>
                        <tr>
                            <th scope="row"><label >{__('Disables / Enables access to author=1 or /author/username ',gsmtc)}</label></th>
                            <td>
                                <ToggleSwitch
                                    value={settings.blocksEnumerationAccess}
                                    onChange={(newValue) => setSettings({...settings,blocksEnumerationAccess : newValue})}
                                />
                                <p className='description'>{(settings.enabled == true ) ? __('Disabled',gsmtc) : __('Enabled',gsmtc)}</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label >{__('Disables / Enables user access through the REST API user endpoint.',gsmtc)}</label></th>
                            <td>
                                <ToggleSwitch
                                    value={settings.hideUsersEndpoints}
                                    onChange={(newValue) => setSettings({...settings,hideUsersEndpoints : newValue})}
                                />
                                <p className='description'>{(settings.enabled == true ) ? __('Disabled',gsmtc) : __('Enabled',gsmtc)}</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label >{__('Disables / Enables the posts author info.',gsmtc)}</label></th>
                            <td>
                                <ToggleSwitch
                                    value={settings.disablePostAuthorInfo}
                                    onChange={(newValue) => setSettings({...settings,disablePostAuthorInfo : newValue})}
                                />
                                <p className='description'>{(settings.enabled == true ) ? __('Disabled',gsmtc) : __('Enabled',gsmtc)}</p>
                            </td>
                        </tr>

                        </tbody> 
                </table>
                <LogedInAlerts
                    logedInAlert={settings.logedInAlert}
                    triggerRoles={settings.triggerRoles}
                    onChange={onChangeTriggerAlerts}
                />
                <div className={alert.class}>
                    <p>{alert.content}</p>
                </div>            
                <p className='submit'>
                    <input type="submit" name="submit-attempts" id="submit-attempts" className="button button-primary" value={ __('Update settings',gsmtc) } style={{marginRight: "25px"}}/>
                    { (isSuperAdmin ) && <input type="button" name="submit-attempts-network" id="submit-attempts-network" className="button button-primary" value={ __('Update all network',gsmtc) }  onClick={onUpdateAttemptsNetwork} /> }
                </p>
            </form>

        </GesimaticAccordion>

        </div>
    )
}