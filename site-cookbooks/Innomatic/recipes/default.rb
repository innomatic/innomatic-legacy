#
# Cookbook Name:: Innomatic
# Recipe:: default
#
# Copyright 2012, Innoteam Srl
#
# All rights reserved - Do Not Redistribute
#

execute "disable-default-site" do
  command "a2dissite default"
end

web_app "innomatic" do
  application_name "innomatic-app"
  docroot "/vagrant/dev"
end