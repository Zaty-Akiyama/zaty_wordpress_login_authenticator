(function ()
{
  function post(path, params, method='post') {

    const form = document.createElement('form');
    form.method = method;
    form.action = path;
  
    for (const key in params) {
      if (params.hasOwnProperty(key)) {
        const hiddenField = document.createElement('input');
        hiddenField.type = 'hidden';
        hiddenField.name = key;
        hiddenField.value = params[key];
  
        form.appendChild(hiddenField);
      }
    }
  
    document.body.appendChild(form);
    form.submit();
  }

  const qrSwitchWrapper = document.querySelector('.qr-area__switch-wrapper')
  const qrSwitch = document.querySelector('.qr-area__switch')
  const qrGenerateButton = document.querySelector('.qr-area__generate-button')
  const qrResetButton = document.querySelector('.qr-area__reset-button')

  const qrAnotherUser = document.querySelector('.qr-area__another-user')
  const qrActivateCheckbox = document.querySelector('#qrActivateCheckbox')

  
  if ( qrSwitchWrapper )
  {
    const truthImage = document.querySelector('.jsTruthImage')
    const dummyImage = document.querySelector('.jsDummyImage')
    const truthText = document.querySelector('.jsTruthText')
    const dummyText = document.querySelector('.jsDummyText')

    qrSwitchWrapper.addEventListener( 'click', e => {
      if( !e.target.classList.contains( 'qr-area__checkbox' ) ) return
      const toggleFlag = qrSwitch.classList.toggle('qr-area__switch--on')

      showImage = toggleFlag ? truthImage : dummyImage
      deleteImage = !toggleFlag ? dummyImage : truthImage

      if( toggleFlag )
      {
        truthImage.style.display = 'block'
        dummyImage.style.display = 'none'
        truthText.style.display = 'block'
        dummyText.style.display = 'none'
      }else
      {
        truthImage.style.display = 'none'
        dummyImage.style.display = 'block'
        truthText.style.display = 'none'
        dummyText.style.display = 'block'
      }
    } )
    qrResetButton.addEventListener( 'click', e => {
      e.preventDefault()
      const confirm = window.confirm('QRコード（シークレットコード）をリセットします。リセットするとGoogle Authenticatorに再登録する必要があります。')
      if( confirm )
      {
        post( '#qrArea', { operation: 'reset' } )
      }
    } )

  } else if ( !qrAnotherUser )
  {
    qrGenerateButton.addEventListener( 'click', e => {
      e.preventDefault()
      post( '#qrArea', { operation: 'generate' } )
    } )
  }

  if( qrActivateCheckbox && ( qrSwitchWrapper || qrAnotherUser ) )
  {
    qrActivateCheckbox.addEventListener( 'change', e => {
      const value = e.target.checked

      const ifActivated = value ? '有効化' : '無効化'

      const confirm = window.confirm('二段階認証を' + ifActivated + 'します。')

      if( confirm )
      {
        post( '#qrArea', { activate: value } )
      }
    })
  }
  
} )()
