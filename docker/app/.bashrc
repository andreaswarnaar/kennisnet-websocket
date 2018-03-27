export serverName=localhost
export PHP_IDE_CONFIG=serverName="localhost"

# Note: PS1 and umask are already set in /etc/profile. You should not
# need this unless you want different defaults for root.

PS1='\[\033[01;36m\]\w\[\033[00m\]\n${debian_chroot:+($debian_chroot)}\[\033[01;36m\]$NAME \D{%T} :: \[\033[00m\]'
# umask 022

# You may uncomment the following lines if you want `ls' to be colorized:
export LS_OPTIONS='--color=auto'
# eval "`dircolors`"
alias ls='ls $LS_OPTIONS'
alias ll='ls $LS_OPTIONS -l'
alias l='ls $LS_OPTIONS -lA'

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

shopt -s checkwinsize

_complete_sf2_app_console() {
    local cur

    COMPREPLY=()
    cur="${COMP_WORDS[COMP_CWORD]}"

    # Assume first word is the actual app/console command
    console="${COMP_WORDS[0]}"

    if [[ ${COMP_CWORD} == 1 ]] ; then
        # No command found, return the list of available commands
        cmds=` ${console}  --no-ansi | sed -n -e '/^Available commands/,//p' | grep -n '^ ' | sed -e 's/^ \+//' | awk '{ print $2 }'`
    else
        # Commands found, parse options
        cmds=` ${console} ${COMP_WORDS[1]} --no-ansi --help | sed -n -e '/^Options/,/^$/p' | grep -n '^ ' | sed -e 's/^ \+//' | awk '{ print $2 }'`
    fi

    COMPREPLY=( $(compgen -W "${cmds}" -- ${cur}) )
    return 0
}

export COMP_WORDBREAKS="\ \"\\'><=;|&("
complete -F _complete_sf2_app_console console