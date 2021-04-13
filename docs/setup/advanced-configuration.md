# Advanced Configuration

## Use jobs to check transcode status

By enabling this option, the job queue won't be blocked by the transcode job for an asset's backend, as individual status check jobs will be scheduled while transcoding is in progress on the backend. This can be useful if the queue hasn't been configured to run in the background, and you're working with large media files which take a long time to transcode. You can also allow this setting to be set from an individual stream asset's configuration.
