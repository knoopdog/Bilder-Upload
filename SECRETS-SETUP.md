# GitHub Secrets Setup for FTP Deployment

This document guides you through adding the required secrets to your GitHub repository for automated FTP deployment to Hostinger.

## Required Secrets

Add the following secrets to your GitHub repository:

1. `FTP_HOST`: `ftp.karlknoop.com`
2. `FTP_USERNAME`: `u613176276.bilderupload`
3. `FTP_PASSWORD`: (Your Hostinger FTP password)
4. `FTP_DIRECTORY`: `/home/u613176276/domains/karlknoop.com/public_html`

## Steps to Add Secrets

1. Go to your GitHub repository: https://github.com/knoopdog/Bilder-Upload
2. Click on **Settings** at the top of the repository (requires admin access)
3. In the left sidebar, click on **Secrets and variables** → **Actions**
4. Click the **New repository secret** button
5. Enter the secret name (e.g., `FTP_HOST`) and its value
6. Click **Add secret**
7. Repeat for each of the secrets listed above

## Security Notes

- GitHub securely encrypts your secrets and only uses them during workflow runs
- Never log or print secret values in your workflow
- Secret values are masked in logs if accidentally printed
- Repository secrets are available to anyone with push access to your repository
- Only enter the actual password in the GitHub UI, not in any files in the repository

## After Adding Secrets

Once you've added all the secrets:

1. Make any change to your repository and push it to the `main` branch
2. Go to the **Actions** tab to monitor the deployment workflow
3. The workflow will automatically deploy your files to Hostinger via FTP

## Troubleshooting

If the deployment fails:

1. Check the workflow logs in the Actions tab
2. Verify all secrets are correctly set
3. Ensure the FTP credentials are active and correct
4. Confirm the target directory exists on the server and is writable
