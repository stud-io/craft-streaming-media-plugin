# Adding Stream Assets

In the top right corner you can select "Create Media Asset" to start adding assets. In the presented form you'll need to set a title, and select a source for the media asset. 

You can choose to upload the file as regular asset, and use the asset as source, or you can specify a source URL, which has to be publically available so the Transcoding Backend can download it.

All stream assets start in draft state, where you can configure the backend(s) and source to use. Once you have everything setup, you can disable the draft toggle in the right sidebar to start the Transcoding process. You won't be able to return to the draft state while the transcoding or transfer process is running.

If transcoding is successful, the Stream Asset will get a status of disabled. In order to use it from the front end, it's needs to be enabled. You can use the lightswitch in the right sidebar to set change an asset's enabled status.

## Require playback authentication

By enabling the require playback authentication lightswitch, the plugin will setup the selected backend to only allow playback for authorized requests. What this means in practice is largely dependent on the storage backend in use, but in general a unique token will be required before a stream asset can be played back from the storage backend. The plugin can in turn generate these tokens, for instance when a stream asset is rendered using a template. 