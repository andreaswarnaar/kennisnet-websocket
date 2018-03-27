# If not running interactively, don't do anything
case $- in
    *i*) ;;
      *) return;;
esac
PS1='\[\033[01;36m\]\w\[\033[00m\]\n${debian_chroot:+($debian_chroot)}\[\033[01;36m\]$NAME \D{%T} :: \[\033[00m\]'
# umask 022

# Some more alias to avoid making mistakes:
#alias rm='rm -i'
#alias cp='cp -i'
#alias mv='mv -i'

HISTCONTROL=ignoreboth
shopt -s histappend
PROMPT_COMMAND="history -a;$PROMPT_COMMAND"
HISTSIZE=4000
HISTFILESIZE=8000
HISTTIMEFORMAT="%d/%m/%y %T "

# check the window size after each command and, if necessary,
# update the values of LINES and COLUMNS.
shopt -s checkwinsize

alias installEncore="yarn add @symfony/webpack-encore jquery sass-loader node-sass webpack-notifier bootstrap@4.0.0-beta.2 font-awesome jquery.easing popper.js rxjs jquery-datetimepicker"
alias watch="/home/node/app/node_modules/.bin/encore dev --watch"