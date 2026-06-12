/*
    Component WpBulkActions

    params: 
        actions => array ( list of actions to show and select)
        onApply => funtion (handler to send the action to parent to execute)

    Task:   This component provide a list of bulk ations similar to wordpress to select and apply an actión in a WpTable context.  
*/
import { useEffect, useState } from 'react'
import { gt } from '../../helpers'
import { sprintf } from '@wordpress/i18n'; // execute 'npm install @wordpress/i18n' to install library

export const WpBulkActions = ({ actions, onApply }) => {
    

    const [selectedOption, setSelectedOption] = useState("-1")
    const [buttonDiabled, setButtonDisabled] = useState(true)

    useEffect(() => {
        if(selectedOption == "-1")
            setButtonDisabled(true)
        else setButtonDisabled(false)
    },[selectedOption])

    const onChangeSelect = (event) => {
        setSelectedOption(event.target.value)
    }

    const onClickButton = () => {
        if (selectedOption != "-1"){
            let string = sprintf( gt('are_you_sure_to_apply_the_action','Are you sure to apply the %1$d action.'), actions[selectedOption]);
            let result = window.confirm(string)
            if(result == true){
                onApply(selectedOption)
            }

        }
    }

   return(
            <>
                <div className="actions bulkactions">
                    <select onChange={onChangeSelect} style={{marginRight:'3px'}}>
                        <option value ="-1">{gt('bulk_actions','Bulk actions')}</option>
                        {actions.map((action,index) => {
                                return( <option value={index}>{action}</option>)
                            }
                        )}
                    </select>
                    <input type="button" className='button action' value={gt('apply','Apply')} onClick={onClickButton} disabled={buttonDiabled}/>
               </div>            
            </>
    )
    
}