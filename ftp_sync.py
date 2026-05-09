"""
FTP 同步工具 - 将本地修改同步到远程服务器
用法: python ftp_sync.py <本地文件路径> [远程文件路径]
      如果省略远程路径，自动根据本地路径计算相对路径
"""
import sys
from ftplib import FTP

FTP_HOST = '47.93.100.255'
FTP_USER = 'www_kaiii_top'
FTP_PASS = '3SwBhp7XBxkRENFj'
LOCAL_BASE = 'E:/think/'


def sync_file(local_path, remote_path=None):
    """上传单个文件到 FTP"""
    if remote_path is None:
        # 从本地路径自动推导远程路径
        if local_path.startswith(LOCAL_BASE):
            remote_path = local_path[len(LOCAL_BASE):].replace('\\', '/')
        else:
            print(f'[ERROR] 无法自动计算远程路径，请传入 remote_path 参数')
            return False

    ftp = FTP(FTP_HOST)
    ftp.login(FTP_USER, FTP_PASS)
    try:
        with open(local_path, 'rb') as f:
            ftp.storbinary(f'STOR {remote_path}', f)
        print(f'[OK] {local_path} -> {remote_path}')
        return True
    except Exception as e:
        print(f'[FAIL] {local_path} -> {remote_path}: {e}')
        return False
    finally:
        ftp.quit()


if __name__ == '__main__':
    if len(sys.argv) < 2:
        print('用法: python ftp_sync.py <本地文件路径> [远程文件路径]')
        sys.exit(1)

    local = sys.argv[1]
    remote = sys.argv[2] if len(sys.argv) > 2 else None
    success = sync_file(local, remote)
    sys.exit(0 if success else 1)
