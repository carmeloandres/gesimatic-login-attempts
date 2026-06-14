/*
    Component WpTableNavigation

    params: 
        items => int ( the number of total items in the table)
        pages => int ( the number to total pages (items per page 0 20))
        page  => int ( the current page)
        onChange => funtion (handler to change the showed page)

    Task:   This component provide a table navigation similar to wp tablenav-pages component to add in tables.  
*/
import { useEffect, useState } from 'react'
import { gt } from '../../helpers'

export const WpTableNAvigation = ({items, pages, page, onChangePage}) => {
    

    const [disabledLeft, setDisabledLeft] = useState(false)
    const [disabledRight, setDisabledRight] = useState(false)
    const [inputDisabled,setInputDisabled] = useState(false);

    useEffect(()=> {
        if (parseInt(page) <= 1)
            setDisabledLeft(true);
        else setDisabledLeft(false);

        if (parseInt(page) == parseInt(pages))
            setDisabledRight(true);
        else setDisabledRight(false);

        if (parseInt(pages) <= 1)
            setInputDisabled(true);
        else setInputDisabled(false);

    },[,page,pages])


    const onChange = ({target}) => {

        const name = target.getAttribute('name');

        switch(name){
            case 'first':
                onChangePage(1);
                break;
            case 'prev':
                if (parseInt(page) > 1)
                    onChangePage(parseInt(page) - 1);
                break;
            case 'next':
                if (parseInt(page) < parseInt(pages))
                    onChangePage(parseInt(page) + 1);
                break;
            case 'last':
                onChangePage(parseInt(pages));
        }
    } 

    const onChangeInput = (event) => {
        event.preventDefault();
        if (parseInt(event.target.value) > parseInt(pages))
            onChangePage(parseInt(pages)); // change to last page
        else if(event.target.value < 1)
                onChangePage(1)
             else onChangePage(parseInt(event.target.value)) 
    }


   return(
            <>
                <div className='tablenav-pages' style={{display:"flex", alignItems:"center"}}>
                    <span className='displaying-num' style={{margin:"0 3px"}}>{items+' '+gt('items','items')} </span>
                    {(items > 0) &&
                    <span className='pagination-links' style={{display: 'flex',alignItems: 'center',gap: '4px'}}>
                        <span className='tablenav-pages-navspan button' name='first' onClick={onChange} disabled={disabledLeft}>{'<<'}</span>
                        <span className='tablenav-pages-navspan button' name='prev' onClick={onChange} disabled={disabledLeft}>{'<'}</span>
                        <span className='paging-input' style={{display: 'inline-flex',alignItems: 'center',gap: '4px'}}>
                            <label htmlFor='current-page-selector' style={{margin:"0 3px"}}>{gt('current_page','Current page')}</label>
                            <input className='current-page' id='current-page-selector' type="number" name="paged" value={page} size="1" min="1" max={pages} onChange={onChangeInput} disabled={inputDisabled} />
                            <span className='tablenav-paging-text' style={{margin:"0 3px"}}>
                                {gt('of','of')}
                                <span className='total-pages'> {pages} </span>
                            </span>
                        </span>
                        <span className='tablenav-pages-navspan button' name="next" onClick={onChange} disabled={disabledRight}>{'>'}</span>
                        <span className='tablenav-pages-navspan button' name="last" onClick={onChange} disabled={disabledRight}>{'>>'}</span>
                    </span>                    
                    }
               </div>            
            </>
    )
    
}