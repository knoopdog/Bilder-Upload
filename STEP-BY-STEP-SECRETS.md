# Step-by-Step Guide to Adding GitHub Secrets

## Steps with Screenshots

1. Click the green "New repository secret" button that appears in the Repository secrets section

2. For each secret, enter the name and value as follows:

   * First secret:
     * Name: `FTP_HOST`
     * Value: `ftp.karlknoop.com`
     * Click "Add secret"

   * Second secret:
     * Name: `FTP_USERNAME`
     * Value: `u613176276.bilderupload`
     * Click "Add secret"

   * Third secret:
     * Name: `FTP_PASSWORD`
     * Value: `H0kwvsX>XfV?AU]*` 
     * Click "Add secret"

   * Fourth secret:
     * Name: `FTP_DIRECTORY`
     * Value: `/home/u613176276/domains/karlknoop.com/public_html`
     * Click "Add secret"

3. After adding all four secrets, they should appear in the list of Repository secrets

4. Once all secrets are added, the GitHub Actions workflow will be able to use them to deploy your site to Hostinger automatically when you push changes to your repository

## What Each Secret Is Used For

* `FTP_HOST`: The FTP server address for your Hostinger account
* `FTP_USERNAME`: Your FTP username for authentication
* `FTP_PASSWORD`: Your FTP password for authentication
* `FTP_DIRECTORY`: The target directory on the server where files should be uploaded

The workflow file (.github/workflows/ftp-deploy.yml) references these secrets using the syntax `${{ secrets.SECRET_NAME }}`.