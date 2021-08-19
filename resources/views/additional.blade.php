<script>
    const Loading = actions.Loading;
    const loading = Loading.create(app);
    const Button = actions.Button;
    const Toast = actions.Toast;
    const Modal = actions.Modal;

    const AppLink = actions.AppLink;
    const NavigationMenu = actions.NavigationMenu;

    const keys = AppLink.create(app, {
        label: 'Keys',
        destination: '/',
    });
    const settingsLink = AppLink.create(app, {
        label: 'Settings',
        destination: '/settings',
    });
    const navigationMenu = NavigationMenu.create(app, {
        items: [keys, settingsLink],
        active: window.location.pathname.indexOf('/settings') !== -1 ? settingsLink : keys,
    });


    const toastSavedKeys = {
        message: 'The keys was saved.',
        duration: 3000,
    };
    const toastSaved = Toast.create(app, toastSavedKeys);

    const toastSavedSelectorsOption = {
        message: 'The keys was saved.',
        duration: 3000,
    };
    const toastSavedSelectors = Toast.create(app, toastSavedSelectorsOption);

    const toastRefreshOptions = {
        message: 'The files was refreshed.',
        duration: 3000,
    };
    const toastRefresh = Toast.create(app, toastRefreshOptions);

    const saveButton = Button.create(app, {label: 'Save Changes'});
    const refreshFiles = Button.create(app, {label: 'Refresh files'});

    const okButton = Button.create(app, {label: 'Refresh Files!'});
    okButton.subscribe(Button.Action.CLICK, () => {

        loading.dispatch(Loading.Action.START);

        utils.getSessionToken(app).then(token => {

            let xhttp = new XMLHttpRequest();

            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    toastRefresh.dispatch(Toast.Action.SHOW);
                    loading.dispatch(Loading.Action.STOP);
                    myModal.dispatch(Modal.Action.CLOSE);
                }
            };

            xhttp.open("POST", "/refresh");
            xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhttp.send(`token=${token}`);

        });

    });

    const cancelButton = Button.create(app, {label: 'Cancel'});
    cancelButton.subscribe(Button.Action.CLICK, () => {
        myModal.dispatch(Modal.Action.CLOSE);
    });

    const modalOptions = {
        title: 'Refresh files',
        message: 'Our plugin generate liquid files which are used to implement our tracking. When you change the theme of your shop, you need to refresh the files.',
        footer: {
            buttons: {
                primary: okButton,
                secondary: [cancelButton],
            },
        },
    };

    const myModal = Modal.create(app, modalOptions);
    refreshFiles.subscribe(Button.Action.CLICK, data => {
        myModal.dispatch(Modal.Action.OPEN);
    });


    // Unsubscribe to click actions
    var TitleBar = actions.TitleBar;
    var titleBarOptions = {
        buttons: {
            primary: saveButton,
            secondary: [refreshFiles]
        },
    };

    TitleBar.create(app, titleBarOptions);


</script>
