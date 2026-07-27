/*
    Component WpFilterActions

    params: 
        filters => object ( list of array, each array a list of option to each filter )
                     example: filters = [[],[]]
        onFilter => funtion (handler to send the filter and option of filter)

    Task:   This component provide one or more list to filter the parent content in a WpTable context..  
*/
import { useEffect, useState } from 'react'
import { gt } from '../../helpers'

export const WpFilterActions = ({filters, onChangeOption, onFilter }) => {
    
    const [selectedFilter, setSelectedFilter] = useState('-1')
    const [selectedOption, setSelectedOption] = useState('-1')
    const [buttonDiabled, setButtonDisabled] = useState(true)

    useEffect(() => {
        if(selectedOption == "-1")
            setButtonDisabled(true)
        else setButtonDisabled(false)
    },[selectedOption])

    const onChangeSelect = (event) => {
        let filter = event.target.name;
        let option = event.target.value;
        if (filter != selectedFilter){
            setSelectedFilter(filter)
            setButtonDisabled(false);
        }
        if(option != selectedOption){
            setSelectedOption(option)
            setButtonDisabled(false)
        }
        onChangeOption();
    }

    const onClickButton = () => {
        if ((selectedFilter != '-1') && (selectedOption != '-1') ){
                onFilter(selectedFilter, selectedOption)
                setButtonDisabled(true);
        }
    }

   return(
            <>
                <div className="actions">
                    {filters.map((filter,index) => {
                        return(
                            <select name={index} onChange={onChangeSelect} style={{marginRight:'3px'}}>
                                    {filter.map((option,key) => {
                                        return(
                                            <option value={key}>{option}</option> 
                                        )
                                    })}
                                </select>

                        )
                            }
                    )}
                    <input type="button" className='button action' value={gt('filter','Filter')} onClick={onClickButton} disabled={buttonDiabled}/>
               </div>            
            </>
    )
    
}