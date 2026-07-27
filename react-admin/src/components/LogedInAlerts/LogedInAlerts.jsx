/*
    Component LogedInAlerts

    params: 
        logedInAlert => boolean ( a boolean to enable or disable the alert)
        availableRoles => object ( an object with pair slug and the translated name of roles)
        triggerRoles => array, with a list of roles that triggers the alert
        translations => the translations object
        onChange => funtion (handler to change the logedInAlert, and set the roles what triggers the alarms)


    Task:   This component enables and diables the logedInAlert and set the roles to triggers the alarms  

*/
import { useContext, useEffect, useState } from 'react'
import { ToggleSwitch } from '../ToggleSwitch/ToggleSwitch';
import { gt } from '../../helpers'

export const LogedInAlerts = ({logedInAlert, triggerRoles,  availableRoles, onChange}) => {
    

    const onChangeTriggeredRole = (value, role) => {
        let newTriggerRoles = [];
        if (value){
            if ( ! triggerRoles.includes(role))
                newTriggerRoles = [...triggerRoles,role]
        } else {
            if ( triggerRoles.includes(role))
                newTriggerRoles = triggerRoles.filter(vector => vector !== role)
        }
        onChange(logedInAlert,newTriggerRoles)
    } 

   return(
            <table className="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><label >{gt('enable_disbles_the_sending','Enable / Disables the sending of alert emails in the user login')}</label></th>
                        <td>
                            <ToggleSwitch
                                value={logedInAlert}
                                onChange={(newValue) => {onChange(newValue, triggerRoles)} }
                            />
                            <p className='description'>{(logedInAlert == true ) ? gt('enabled','Enabled') : gt('disabled','Disabled')}</p>
                        </td>
                    </tr>
                    { logedInAlert &&
                        Object.entries(availableRoles).map(([key, value]) => {
                            return(
                                <tr>
                                <th scope="row"><label style={{textAlign: "right", display:"block"}}>{value+' '+'role'}</label></th>
                                <td>
                                    <ToggleSwitch
                                        value={(triggerRoles.includes(key)) ? true : false }
                                        onChange={(newValue) => {onChangeTriggeredRole(newValue, key)}}
                                    />
                                    <p className='description'>{(triggerRoles.includes(key)) ? gt('enabled','Enabled') : gt('disbled','Disabled')}</p>
                                </td>
                            </tr>        
                            
                        )})
                    }
                </tbody> 
            </table>            
        )
    
}