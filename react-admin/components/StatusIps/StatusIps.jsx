/*
    Component StatusIps

    params: 
        restUrl => the url to the rest Api
        nonce => the nonce to validate the Api requests

    Task: This component manage the status ip table/information   

*/
import { useEffect, useState } from 'react'
import { gt, doStatusIpsAction, getPagination, getStatusIps, getStringDate } from '../../helpers'
import { WpBulkActions } from '../WpBulkActions/WpBulkActions'
import { WpFilterActions } from '../WpFilterActions/WpFilterActions'
import { WpTableNAvigation } from '../WpTableNavigation/WpTableNavigation'
import { sprintf } from '@wordpress/i18n'; // execute 'npm install @wordpress/i18n' to install library
import { icons } from '../icons'
import './StatusIps.css'

export const StatusIps = ({restUrl, nonce }) => {
    

    // Estado para almacenar la clase y el contenido de las alertas
    const [alert,setAlert] = useState({class:'gsmtc-display-none' ,content:''});

    const [query, setQuery] = useState({page : 1, orderAttempts : '', orderLockPeriod: '', orderLastAttempt: 'desc', filterStatus : ''});

    const [statusIps, setStatusIps] = useState([])

    const [dataPagination,setDataPagination] = useState({items: 1, pages: 1})

    const { items, pages } = dataPagination;

    const[allBulk, setAllBulk] = useState(false);

    const actionString = {reset:gt('reset_ip','Reset ip'), unlock:gt('unlock_ip','Unlock ip')};
    const bulkActions = [gt('reset','Reset'),gt('unlock','Unlock')];
    const filterActions = [ [ gt('all_states','All states'), gt('enabled','enabled'),gt('blocked','blocked')] ]


    useEffect( () => {
        updateStatus()
    },[,query])

    const onClickIcon = (name) => {
        switch(name){
            case 'upAttempts' :
                setQuery({...query,orderAttempts : 'asc', orderLastAttempt: '', orderLockPeriod : ''});
                break;
            case 'downAttempts' :
                setQuery({...query,orderAttempts : 'desc', orderLastAttempt: '', orderLockPeriod : ''});
                break;
            case 'upLastAttempt' :
                setQuery({...query,orderAttempts : '', orderLastAttempt: 'asc', orderLockPeriod : ''});
                break;
            case 'downLastAttempt' :
                setQuery({...query,orderAttempts : '', orderLastAttempt: 'desc', orderLockPeriod : ''});
                break;
            case 'upNextLock' :
                setQuery({...query,orderAttempts : '', orderLastAttempt: '', orderLockPeriod : 'asc'});
                break;
            case 'downNextLock' :
                setQuery({...query,orderAttempts : '', orderLastAttempt: '', orderLockPeriod : 'desc'});
                break;
        }
    }

    const onApplyFilter = (filter,option) => {

        // we only have on filter, then we don't need check what is the selected filter, only the option
        switch(option){
            case '0' :
                setQuery({...query,filterStatus : ''});
                break;
            case '1' :
                setQuery({...query,filterStatus : 'enabled'});
                break;
            case '2' :
                setQuery({...query,filterStatus : 'disabled'});
                break;
        }
    }



    const updateStatus = async() => {

        setAlert({class:'gsmtc-notice gsmtc-notice-warning',content:gt('getting_the_information','Getting the information.. Please wait')});

        const status = await getStatusIps(restUrl,nonce, query);
        let newStatus = []
        status.forEach(element => {
            let newElement = {...element,action : false}
            newStatus = [...newStatus,newElement]            
        });
        const newPagination = await getPagination(restUrl,nonce, 'login_status_ip', query);
        setStatusIps(newStatus);
        setDataPagination(newPagination);

        setAlert({class:'gsmtc-notice gsmtc-notice-success',content:gt('the_information_has_been_obtained','The information has been obtained correctly')})
        setTimeout(() => {
            setAlert({class:'gsmtc-notice-fade-out',content:gt('the_information_has_been_obtained','The information has been obtained correctly')});
            setTimeout(() => {setAlert({class:'gsmtc-display-none',content:''})},1000);
        },4000);


    }


    const onClickAction = async (event) => {
        
        let ids = event.target.id.split('-');
        let action = '';
        let string = '';
        if (event.target.innerHTML == actionString.unlock){
            string = sprintf( gt('are_you_sure_to_apply_the_action','Are you sure to apply the %1$d action.'), actionString.unlock);
            action = 'unLock';
        } else {
            string = sprintf( gt('are_you_sure_to_apply_the_action','Are you sure to apply the %1$d action.'), actionString.reset);
            action = 'reset';
        } 

        let result = window.confirm(string)
        if(result == true){
            let id = [ids[1]];

            setAlert({class:'gsmtc-notice gsmtc-notice-info',content:gt('performing_the_action','Performing the action.. Please wait')});
            let doResult = await doStatusIpsAction(restUrl,nonce,action,id)

            if (doResult){

                updateStatus();
                    
                setAlert({class:'gsmtc-notice gsmtc-notice-success',content:gt('the_action_has_been_performed_correctly','The action has been performed correctly')})
                setTimeout(() => {
                    setAlert({class:'gsmtc-notice gsmtc-notice-success',content:gt('the_action_has_been_performed_correctly','The action has been performed correctly')})
                    setTimeout(() => {setAlert({class:'gsmtc-display-none',content:''})},1000);
                },4000);

            } else setAlert({class:'gsmtc-notice gsmtc-notice-error',content:gt('the_action_has_not_been_performed_correctly','The action has not been performed correctly')})
        }
    }

    const onApplyAction = async (action) => {

        setAlert({class:'gsmtc-notice gsmtcnotice-info fade-in',content:gt('performing_the_action','Performing the action.. Please wait')});

        let ids = [];
        statusIps.forEach((status) => {
            if (status.action)
                ids = [...ids, status.id]
        })

        let doAction = '';
        switch(action){
            case '0' :
                doAction = 'reset';
                break;
            case '1' :
                doAction = 'unLock';
                break;
        }

        const result = await doStatusIpsAction(restUrl,nonce,doAction,ids);
        if (result){
            updateStatus();
            setAlert({class:'gsmtc-notice gsmtc-notice-success',content:gt('the_action_has_been_performed_correctly','The action has been performed correctly')})
            setTimeout(() => {
                setAlert({class:'gsmtc-notice gsmtc-notice-success',content:gt('the_action_has_been_performed_correctly','The action has been performed correctly')});
                setTimeout(() => {setAlert({class:'gsmtc-display-none',content:''})},1000);
            },4000);

        } else setAlert({class:'gsmtc-notice gsmtc-notice-error',content:gt('the_action_has_not_been_performed_correctly','The action has not been performed correctly')})

    }


    const onChangeCheckbox = (event) => {

        let id = event.target.name.split('-');
        let newStatusIps = [];
        let newStatus = {};
        
        if (id[1] == '0'){
            statusIps.forEach((status) => {
                newStatus = {...status,action : ! allBulk}
                newStatusIps = [...newStatusIps, newStatus] 
            })
            
            setAllBulk( ! allBulk);
        } else {
            statusIps.forEach((status) => {
                if (status.id == id[1]){
                    newStatus = {...status, action : ! status.action}
                    newStatusIps = [...newStatusIps, newStatus]
                } else newStatusIps = [...newStatusIps, status] 
            })
        }
        setStatusIps(newStatusIps)
    }


    return(
            <>
                <div className='gsmtc-status-ips'>
                    <div className={alert.class}>
                        <p>{alert.content}</p>
                    </div>            
                    <div style={{display: 'flex', justifyContent:'space-between', margin: '3px 0'}}>
                        <WpBulkActions
                            actions={bulkActions}
                            onApply={onApplyAction}
                        />
                        <WpFilterActions
                            filters={filterActions}
                            onChangeOption={() => setQuery({...query,page: 1})}
                            onFilter={onApplyFilter}
                        />
                        <WpTableNAvigation 
                            items={items}
                            pages={pages}
                            page={query.page}
                            onChangePage={(newPage) => setQuery({...query,page: newPage})}
                        />
                    </div>
                    <table className='wp-list-table widefat'>
                        <thead>
                            <tr>
                                <td id="index"><input id={'0'} type="checkbox" name="checkbox-0" onChange={onChangeCheckbox} checked = {allBulk}/></td>
                                <th scope="col" className='manage-column column-title'>{gt('user_login','User login')}</th>
                                <th scope="col" className='manage-column column-title'>Ip</th>
                                <th scope="col" className='manage-column column-title'><div style={{display:'flex'}}><span >{gt('attempts','Attempts')}</span><span className='sort'><icons.caret_up_fill className={(query.orderAttempts == 'asc')? 'sort-asc selected' : 'sort-asc'} name='upAttempts' onClick={onClickIcon}/><icons.caret_down_fill className={(query.orderAttempts == 'desc')? 'sort-desc selected' : 'sort-desc'} name='downAttempts' onClick={onClickIcon}/></span></div></th>
                                <th scope="col" className='manage-column column-title'><div style={{display:'flex'}}><span>{gt('last_attempts','Last attempt')}</span><span className='sort'><icons.caret_up_fill className={(query.orderLastAttempt == 'asc')? 'sort-asc selected' : 'sort-asc'} name='upLastAttempt' onClick={onClickIcon}/><icons.caret_down_fill className={(query.orderLastAttempt == 'desc')? 'sort-desc selected' : 'sort-desc'} name='downLastAttempt'onClick={onClickIcon}/></span></div></th>
                                <th scope="col" className='manage-column column-title'><div style={{display:'flex'}}><span>{gt('next_lock_period','Next lock period')}</span><span className='sort'><icons.caret_up_fill className={(query.orderLockPeriod == 'asc')? 'sort-asc selected' : 'sort-asc'} name='upNextLock' onClick={onClickIcon}/><icons.caret_down_fill className={(query.orderLockPeriod == 'desc')? 'sort-desc selected' : 'sort-desc'} name='downNextLock'onClick={onClickIcon}/></span></div></th>
                                <th scope="col" className='manage-column column-title'>{gt('state','State')}</th>
                                <th scope="col" className='manage-column column-title'>{gt('action','Action')}</th>
                                <th scope="col" className='manage-column column-title'>{gt('blocked_until','Blocked until')}</th>
                            </tr>
                        </thead>
                        <tbody>
                            { (statusIps.length == 0)? <tr><td colspan="9" style={{ fontWeight: 'bold', textAlign: 'center', width: '100%'}}>{gt('there_are_no_status_ips_to_show','There are no status ips to show')}</td></tr> : ''}
                            { (statusIps.length > 0)?                             
                            <>
                                {statusIps.map((status, index) => {
                                    let dateString = '';
                                    let seconds = 0;
                                    if (status.lockUntil > 0){
                                        let date = new Date();
                                        seconds = date.getSeconds() + parseInt(status.lockUntil) 
                                        dateString = getStringDate(seconds);    
                                    }
                                    let date = new Date();
                                    seconds = date.getSeconds() - parseInt(status.lastAttempt)
                                    let lastAttempt = getStringDate(seconds);
                                    return(
                                        <tr>
                                            <th scope="row"><input id={status.id} type="checkbox" name={"checkbox-"+status.id} onChange={onChangeCheckbox} checked={status.action}/></th>
                                            <td style={{verticalAlign: 'middle'}}>{status.userLogin}</td>
                                            <td style={{verticalAlign: 'middle'}}>{status.ip}</td>
                                            <td style={{verticalAlign: 'middle'}}>{status.attempts}</td>
                                            <td style={{verticalAlign: 'middle'}}>{lastAttempt}</td>
                                            <td style={{verticalAlign: 'middle'}}>{status.currentPeriod+' '+gt('min','min')}</td>
                                            <td style={{verticalAlign: 'middle'}}>{(status.status == 'enabled') ? gt('enabled','enabled') : gt('bloqued','bloqued')}</td>
                                            <td><button className='button button-primary' id={'statusId-'+status.id} onClick={onClickAction}>{(status.status == 'enabled') ? actionString.reset : actionString.unlock}</button></td>
                                            <td style={{verticalAlign: 'middle'}}>{dateString}</td>
                                        </tr>
                                        )
                                    })
                                }
                           </> : ''}
                        </tbody>
                    </table>
               </div>            
            </>
    )
    
}