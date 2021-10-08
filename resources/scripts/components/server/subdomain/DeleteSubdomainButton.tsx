import React, { useState } from 'react';
import { ServerContext } from '@/state/server';
import { Actions, useStoreActions } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import { httpErrorToHuman } from '@/api/http';
import Button from '@/components/elements/Button';
import ConfirmationModal from '@/components/elements/ConfirmationModal';
import deleteServerSubdomain from '@/api/server/subdomain/deleteServerSubdomain';

interface Props {
    subdomainId: number;
    onDeleted: () => void;
}

export default ({ subdomainId, onDeleted }: Props) => {
    const [ visible, setVisible ] = useState(false);
    const [ isLoading, setIsLoading ] = useState(false);
    const uuid = ServerContext.useStoreState(state => state.server.data!.uuid);
    const { addError, clearFlashes } = useStoreActions((actions: Actions<ApplicationStore>) => actions.flashes);

    const onDelete = () => {
        setIsLoading(true);
        clearFlashes('server:subdomain');

        deleteServerSubdomain(uuid, subdomainId)
            .then(() => {
                setIsLoading(false);
                setVisible(false);
                onDeleted();
            })
            .catch(error => {
                console.error(error);

                addError({ key: 'server:subdomain', message: httpErrorToHuman(error) });

                setIsLoading(false);
                setVisible(false);
            });
    };

    return (
        <>
            <ConfirmationModal
                visible={visible}
                title={'Delete subdomain?'}
                buttonText={'Yes, delete subdomain'}
                onConfirmed={onDelete}
                showSpinnerOverlay={isLoading}
                onModalDismissed={() => setVisible(false)}
            >
                Are you sure you want to delete this subdomain?
            </ConfirmationModal>
            <Button color={'red'} size={'xsmall'} isSecondary onClick={() => setVisible(true)}>
                Delete
            </Button>
        </>
    );
};
