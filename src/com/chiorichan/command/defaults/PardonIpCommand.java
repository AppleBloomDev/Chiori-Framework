package com.chiorichan.command.defaults;

import java.util.ArrayList;
import java.util.List;

import org.apache.commons.lang.Validate;
import com.chiorichan.ChioriFramework;
import com.chiorichan.ChatColor;
import com.chiorichan.command.Command;
import com.chiorichan.command.CommandSender;
import com.chiorichan.util.StringUtil;

import com.google.common.collect.ImmutableList;

public class PardonIpCommand extends VanillaCommand {
    public PardonIpCommand() {
        super("pardon-ip");
        this.description = "Allows the specified IP address to use this server";
        this.usageMessage = "/pardon-ip <address>";
        this.setPermission("bukkit.command.unban.ip");
    }

    @Override
    public boolean execute(CommandSender sender, String currentAlias, String[] args) {
        if (!testPermission(sender)) return true;
        if (args.length != 1)  {
            sender.sendMessage(ChatColor.RED + "Usage: " + usageMessage);
            return false;
        }

        if (BanIpCommand.ipValidity.matcher(args[0]).matches()) {
            ChioriFramework.unbanIP(args[0]);
            Command.broadcastCommandMessage(sender, "Pardoned ip " + args[0]);
        } else {
            sender.sendMessage("Invalid ip");
        }

        return true;
    }

    @Override
    public List<String> tabComplete(CommandSender sender, String alias, String[] args) throws IllegalArgumentException {
        Validate.notNull(sender, "Sender cannot be null");
        Validate.notNull(args, "Arguments cannot be null");
        Validate.notNull(alias, "Alias cannot be null");

        if (args.length == 1) {
            return StringUtil.copyPartialMatches(args[0], ChioriFramework.getIPBans(), new ArrayList<String>());
        }
        return ImmutableList.of();
    }
}
