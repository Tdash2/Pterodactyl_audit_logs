import React from 'react';
import tw from 'twin.macro';
import Can from '@/components/elements/Can';
import Button from '@/components/elements/Button';
import StopOrKillButton from '@/components/server/StopOrKillButton';
import { PowerAction } from '@/components/server/ServerConsole';
import { ServerContext } from '@/state/server';
import isEqual from 'react-fast-compare';
import { useStoreState } from 'easy-peasy';
import { LinkButton } from '@/components/elements/Button';

const PowerControls = () => {
    const status = ServerContext.useStoreState(state => state.status.value);
    const instance = ServerContext.useStoreState(state => state.socket.instance);
    const username = useStoreState(state => state.user.data!.username);
    const id = ServerContext.useStoreState(state => state.server.data!.id);
    const sftp = ServerContext.useStoreState(state => state.server.data!.sftpDetails, isEqual);


    const sendPowerCommand = (command: PowerAction) => {
        instance && instance.send('set state', command);
    };

    return (
        <div css={tw`shadow-md bg-neutral-700 rounded p-3 flex flex-wrap text-xs justify-center justify-around`}>
            <Can action={'control.start'}>
                <Button
                    size={'xsmall'}
                    color={'green'}
                    isSecondary
                    disabled={status !== 'offline'}
                    css={tw`sm:flex-grow lg:flex-grow-0`}
                    onClick={e => {
                        e.preventDefault();
                        sendPowerCommand('start');
                    }}
                >
                    Start
                </Button>
            </Can>
            <Can action={'control.restart'}>
                <Button
                    size={'xsmall'}
                    isSecondary
                    disabled={!status}
                    css={tw`sm:flex-grow lg:flex-grow-0`}
                    onClick={e => {
                        e.preventDefault();
                        sendPowerCommand('restart');
                    }}
                >
                    Restart
                </Button>
            </Can>
            <Can action={'control.stop'}>
                <StopOrKillButton onPress={action => sendPowerCommand(action)}/>
            </Can>
            <Can action={'control.stop'}>
                <LinkButton
                    size={'xsmall'}
                    color={'primary'}
                    isSecondary
                    css={tw`sm:flex-grow lg:flex-grow-0`}
                    onClick={e => {
                        
                        sendPowerCommand('kill');
                    }}
                >
                    Kill
        </LinkButton>
            </Can>
        </div>
    );
};

export default PowerControls;
