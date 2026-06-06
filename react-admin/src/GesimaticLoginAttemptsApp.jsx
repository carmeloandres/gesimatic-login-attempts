import { useEffect, useState } from 'react'
import { gt } from '../helpers';
//import { GsmtcAccordion, TestEmail, ToggleSwitch, WpPasswordInput } from './components';
//import { getSmtpSettings, gt, setSmtpSettings } from './helpers'
import './GesimaticLoginAttemptsApp.css'

export const GesimaticLoginAttemptsApp = () => {

  // It gets the credentials for access to the API
  const { restUrl, nonce, isSuperAdmin } = gesimaticLoginAttemptsAdmin;

  // state to manage the smtp settings
  const [settings, setSettings] = useState({
        enabled : false, // Enables or Disables the smtp functionality
        host : '', // The smtp server url
        port : 587, // the smtp port, 587, 465 or 25 it depends of your server 
        userName : '', // The smtp username
        password: '', //  the smtp password
        secure: 'tls', // tls or ssl
        from : '',  // from email address
        fromName : '', // replace with the from name
    });

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
        </div>
    )
}